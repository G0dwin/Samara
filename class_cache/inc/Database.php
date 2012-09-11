<?php Samara_Include('Vars', 'inc');
Samara_Include('UpdateManager', 'inc');

class Database
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
		$rows = $connection->query(Query::IsA($query) ? $query->Compile() : $query);
		if ($rows === FALSE)
		{
			$this->last_error = $connection->error;
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
				'InvertBits' => '~%s',
				'Mod' => '%s %% %s',
				'InvertBits' => '~%s',
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
				'IsIn' => 'IN(%l)'
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
			return str_replace('%l', $this->FormatList($args), $op);
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
	
}

Samara_Include('Join', 'inc/queries');
Samara_Include('SelectQuery', 'inc/queries');
