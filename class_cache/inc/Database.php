<?php 

Samara_Include('Vars', 'inc');
Samara_Include('UpdateManager', 'inc');

class Database extends SamaraBase
{
	protected static $instance;
	protected static $aggreate_functions;
	protected static $operators;
	protected static $compare_operators;
	protected static $logical_operators;
	protected $conection;
	protected $last_error;
	
	protected function __construct()
	{
		
	}
	
	public static function DB()
	{
		return Database::$instance ?: (Database::$instance = new Database());
	}
	
	protected function getConnection()
	{
		$saved_connector = $this->conection;
		if (!$saved_connector)
		{
			global $samara_db_info;
			$saved_connector = $this->conection = new mysqli(
											$samara_db_info['host'],
											$samara_db_info['username'],
											$samara_db_info['passwd'],
											$samara_db_info['dbname'],
											$samara_db_info['port'],
											$samara_db_info['socket']
					);
			if (mysqli_connect_errno())
			{
				return mysqli_connect_error();
			}
		}
		return $saved_connector;
	}
	
	protected function DBInfo()
	{
		return var_export(UpdateManager::CreateUpdateScript(), TRUE);//var_export(Update, TRUE);
	}
	
	public static function __callStatic($name, $args)
	{
		$instance = Database::DB();
		$exists = method_exists($instance, $name);
		$class = get_class($instance);
		if ($exists && $name[0] >= 'A' && $name[0] <= 'Z')
		{
			return call_user_func_array(array($instance, $name), $args);
		}
		if ($exists)
		{
			throw new \Exception("The method $class::$name is not public");
		}
		throw new \Exception("The method $class::$name does not exist");
	}
	
	protected function ExecuteQuery($query)
	{
		$connection = $this->getConnection();

		/*static $id = 1;
		print_r(is_array($query) ? implode("<br />\n", $query) : (Query::IsA($query) ? $query->Compile() : $query));
		echo "<br />\n";
		/*return $id++;*/
		$error = false;
		if (is_array($query))
		{
			if (empty($query))
			{
				return true;
			}
			$rows = array();
			if ($connection->multi_query(implode("\n", $query)))
			{
				$i = 0;
				do {
					$i++;
				} while ($connection->next_result());
				$error = $connection->errno;
			}
			else
			{
				$error = true;
			}
			//$error = !$rows;
			/*if (!$error)
			{
				do
				{
					$new = $connection->use_result();
					if ($new === false)
					{
						$error = true;
					}
					else
					{
						$rows[] = $new;
					}
					if ($error)
					{
						die('wtf? '.$new);
					}
				}
				while (!$error && $connection->next_result());
			}*/
		}
		else
		{
			$error = !($rows = $connection->query(Query::IsA($query) ? $query->Compile() : $query));
		}
		if ($error)
		{
			print_r($_POST);
			$this->last_error = $connection->error;
			print_r(debug_backtrace());
			print_r($query);
			die('Error: '.$connection->error);
			return FALSE;
		}
		if (is_object($rows))
		{
			$result = array();
			while (($row = $rows->fetch_assoc()) !== NULL)
			{
				$result[] = $row;
			}
			return $result;
		}
		return $rows;
	}
	
	protected function GetLatestID()
	{
		return $this->getConnection()->insert_id;
	}
	
	protected function GetLastError()
	{
		return $this->last_error;
	}
	
	protected function FormatNull()
	{
		return 'NULL';
	}
	
	protected function FormatInteger($value)
	{
		return $value === null ? $this->FormatNull() : $value;
	}
	
	protected function FormatString($value)
	{
		return $value === null ? $this->FormatNull() : "'$value'";
	}

	protected function FormatAll()
	{
		return '*';
	}
	
	protected function FormatTable($value, $alias = NULL)
	{
		return "`$value`".($alias ? $this->FormatAlias($alias) : '');
	}
	
	protected function FormatColumn($value, $alias = NULL)
	{
		return $this->FormatTable($value).($alias ? $this->FormatAlias($alias) : '');
	}
	
	protected function FormatSetExpression($column, $value)
	{
		return $this->FormatTable($column).' = '.$value;
	}
	
	protected function FormatAlias($alias)
	{
		return ' AS '.$this->FormatTable(Samara_ToUnderscoreCase($alias));
	}
	
	protected function TableColumn($table, $column)
	{
		return "$table.$column";
	}

	protected function CompleteQuery($query)
	{
		return $query.';';
	}
	
	protected function GetAggreateFunctions()
	{
		if (!Database::$aggreate_functions)
		{
			Database::$aggreate_functions = array(
					'Max' => 'MAX(%s)',
					'Min' => 'MIN(%s)',
					'Average' => 'AVG(%s)',
					'Sum' => 'SUM(%s)',
					'Count' => 'COUNT(%s)'
			);
		}
		return Database::$aggreate_functions;
	}
	
	protected function GetOperators()
	{
		if (!Database::$operators)
		{
			Database::$operators = array(
				'Plus' => '%s + %s',
				'Minus' => '%s - %s',
				'Times' => '%s * %s',
				'DividedBy' => '%s / %s',
				'BitwiseAnd' => '%s & %s',
				'BitwiseOr' => '%s | %s',
				'BitwiseXOr' => '%s ^ %s',
				'InvertBits' => '~~%s',
				'Mod' => '%s %% %s',
				'InvertBits' => '~~%s',
				'Negate' => '-%s'
			);
		}
		return Database::$operators;
	}
	
	protected function GetComparisonOperators()
	{
		if (!Database::$compare_operators)
		{
			Database::$compare_operators = array(
				'Equals' => '%s = %s',
				'LessThan' => '%s < %s',
				'GreaterThan' => '%s > %s',
				'LessThanOrEquals' => '%s <= %s',
				'GreaterThanOrEquals' => '%s >= %s',
				'DoesNotEqual' => '%s <> %s',
				'IsNull' => '%s IS NULL',
				'IsNotNull' => '%s IS NOT NULL',
				'IsBetween' => '%s BETWEEN %s AND %s',
				'Coalesce' => 'COALESCE(%l)',
				'IsOneOf' => '%s IN (%l)',
				'IsNotOneOf' => '%s NOT IN (%l)'
			);
		}
		return Database::$compare_operators;
	}
	
	protected function GetLogicalOperators()
	{
		if (!Database::$logical_operators)
		{
			Database::$logical_operators = array(
				'And' => '%s AND %s',
				'Or' => '%s OR %s'
			);
		}
		return Database::$logical_operators;
	}
	
	/*protected function FormatFunction($name, $args)
	{
		return "$name(".implode(', ', $args).')';
	}*/
	
	protected function Format($op, $args)
	{
		if (preg_match('/(^|[^%])%l/', $op))
		{
			if (!preg_match('/(^|[^%])%s/', $op))
			{
				return str_replace('%l', $this->FormatList($args), $op);
			}
			$op = str_replace('%l', $this->FormatList($args[1]), $op);
		}
		return call_user_func_array('sprintf', array_merge(array($op), $args));
	}
	
	protected function FormatList($array)
	{
		return implode(', ', $array);
	}
	
	protected function AddBrackets($expression)
	{
		return "($expression)";
	}
	
	protected function FormatAndExpression($arg1, $arg2)
	{
		return "$arg1 AND $arg2";
	}
	
	protected function FormatOrExpression($arg1, $arg2)
	{
		return "$arg1 OR $arg2";
	}
	
	protected function Select()
	{
		$reflector = new ReflectionClass('SelectQuery');
		return $reflector->newInstanceArgs(func_get_args());
	}
	
	protected function Insert()
	{
		$reflector = new ReflectionClass('InsertQuery');
		return $reflector->newInstanceArgs(func_get_args());
		//return $result ? $this->GetLatestID() : $result;
	}
	
	protected function Update()
	{
		$reflector = new ReflectionClass('UpdateQuery');
		return $reflector->newInstanceArgs(func_get_args());
	}
	
	protected function Delete()
	{
		$reflector = new ReflectionClass('DeleteQuery');
		return $reflector->newInstanceArgs(func_get_args());
	}
	
	protected function CreateTable($table_name, $columns)
	{
		return $this->CompleteQuery(
				'CREATE TABLE '.$this->FormatTable($table_name).
					' ('.implode(', ', $columns).") COLLATE='utf8_general_ci' ENGINE=InnoDB");
	}
	
	protected function AlterTable($table_name, $columns)
	{
		return $this->CompleteQuery('ALTER TABLE '.$this->FormatTable($table_name).' '.implode(', ', $columns));
	}
	
	protected function FormatAddColumn($column_data)
	{
		return 'ADD COLUMN '.$column_data;
	}
	
	protected function FormatModifyColumn($column_data)
	{
		return 'MODIFY COLUMN '.$column_data;
	}
	
	protected function FormatDropColumn($column)
	{
		return 'DROP COLUMN '.$this->FormatColumn($column);
	}
	
	protected function DescribeTable($table_name)
	{
		return Database::CompleteQuery('DESCRIBE '.Database::FormatTable($table_name));
	}
	
	protected function GetAllTables()
	{
		return Database::CompleteQuery('SHOW TABLES');
	}
	
}

Samara_Include('JoinStatement', 'inc/queries');
Samara_Include('SelectQuery', 'inc/queries');
Samara_Include('DeleteQuery', 'inc/queries');
Samara_Include('InsertQuery', 'inc/queries');
Samara_Include('UpdateQuery', 'inc/queries');
