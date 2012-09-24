<?php 

Samara_Include('Database', 'inc');

class UpdateManager extends SamaraBase
{
	protected static $instance;
	
	protected function __construct()
	{
		
	}
	
	public static function __callStatic($name, $args)
	{
		return call_user_func_array(array(UpdateManager::getInstance(), $name), $args);
	}
	
	protected static function getInstance()
	{
		return UpdateManager::$instance ?: (UpdateManager::$instance = new UpdateManager());
	}
	
	protected function GetDomainDir()
	{
		return 'inc/domain';
	}

	protected function GetTableList()
	{
		$tables = array();
		$table_data = Database::ExecuteQuery(Database::GetAllTables());
		foreach ($table_data AS $table)
		{
			$key = array_keys($table);
			$tables[] = $table[$key[0]];
		}
		return $tables;
	}
	
	protected function GetDomainObjectList()
	{
		$files = array_merge(glob(SAMARA_ROOT.$this->GetDomainDir().'/*.sphp'), glob(SAMARA_ROOT.'extensions/*/domain/*.sphp'));
		$classes = array();
		foreach ($files as $file)
		{
			$classes[] = preg_replace('/^(.*[\/|\\\\])?([^\/|^\\\\]*)\.sphp$/', '$2', $file);
		}
		//die(var_export($classes, true));
		return $classes;
	}
	
	protected function CreateCreateScript($table_name, $properties, $tables)
	{
		$sql = array();
		$columns = array();
		foreach ($properties AS $property)
		{
			/*$script = $property->CompileForPreCreate($tables, $columns);
			if ($script)
			{
				$sql[] = $script;
			}*/
			$script = $property->CompileForCreate($tables);
			if ($property->IsColumn())
			{
				if ($script)
				{
					$columns[] = $script;
				}
			}
			else
			{
				$sql = array_merge($sql, $script);
			}
		}
		return array_merge(array(Database::CreateTable($table_name, $columns)), $sql);//Database::CompleteQuery('CREATE TABLE '.Database::FormatTable($table_name).' ('.implode(', ', $columns).") COLLATE='utf8_general_ci' ENGINE=InnoDB");
	}
	
	protected function CreateModifyScript($table_name, $properties, $tables)
	{
		$sql = array();
		$new_column_list = array();
		$columns = $this->GetTableInfo($table_name);
		$mods = array();
		foreach ($properties AS $property)
		{
			/*$script = $property->CompileForPreCreate($tables, $columns);
			if ($script)
			{
				$sql[] = $script;
			}*/
			$column_name = $property->GetNativeName();
			if ($property->IsColumn())//$column_name)
			{
				if (!isset($columns[$column_name]))
				{
					$mods[] = Database::FormatAddColumn($property->CompileForCreate($tables));
				}
				else if ($property->IsOutdated($columns[$column_name]))
				{
					$mods[] = Database::FormatModifyColumn($property->CompileForCreate($tables));
				}
				$new_column_list[] = $column_name;
			}
			else
			{
				$sql = array_merge($sql, $property->CompileForCreate($tables));
			}
		}
		$drop = array_diff(array_keys($columns), $new_column_list);
		if ($drop)
		{
			foreach ($drop as $column)
			{
				$mods[] = Database::FormatDropColumn($column);
			}
		}
		if ($mods)
		{
			$sql = array_merge(array(Database::AlterTable($table_name, $mods)), $sql);
		}
		return $sql;
	}
	
	protected function CreateUpdateScriptForTable($table_name, $properties, $tables)
	{
		$sql = array();
		if (array_search($table_name, $tables) === FALSE)
		{
			$sql = $this->CreateCreateScript($table_name, $properties, $tables);
		}
		else
		{
			$sql = $this->CreateModifyScript($table_name, $properties, $tables);
		}
		return $sql;
	}
	
	protected function CreateUpdateScript()
	{
		$tables = $this->GetTableList();
		$classes = $this->GetDomainObjectList();
		$sql = array();
		
		foreach ($classes as $class)
		{
			Samara_Include($class, $this->GetDomainDir());
			//$class = Samara_GetFullClassName($class);
			$object = new $class();
			$table_name = $object->NativeName();
			$properties = $object->GetProperties();
			
			$sql = array_merge($sql, $this->CreateUpdateScriptForTable($table_name, $properties, $tables));
		}
		return $sql ?: FALSE;
	}
	
	protected function GetTableInfo($table_name)
	{
		$columns = array();
		$table_data = Database::ExecuteQuery(Database::DescribeTable($table_name));
		foreach ($table_data AS $column)
		{
			$columns[$column['Field']] = $column;
		}
		return $columns;
	}
	
}

