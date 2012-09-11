<?php Samara_Include('DataObject', 'inc');
Samara_Include('ColumnReference', 'inc/queries');
Samara_Include('Join', 'inc/queries');
Samara_Include('Insert', 'inc/queries');
Samara_Include('Update', 'inc/queries');
Samara_Include('Identifier', 'inc/primitive_types');

abstract class DomainObject {
	
	protected $properties;
	protected $last_alias;
	
	public function __construct()
	{
		$this->Properties();
		$args = func_get_args();
		if ($args)
		{
			call_user_func_array(array($this, 'Load'), $args);
		}
	}
		
	public static function NativeName()
	{
		return Samara_ToUnderscoreCase(Samara_GetClassName(get_called_class()));
	}
	
	protected function Properties()
	{
		if ($this->properties)
		{
			throw new Exception('parent::ConstructProperties() must be called first and ConstructProperties() may only be called once.');
		}
		$this->AddProperty(new Identifier());
	}
	
	public function GetProperties()
	{
		return $this->properties;
	}
	
	public function Save()
	{
		$type = get_called_class();
		$values = $this->GetProperties();
		$properties = array();
		foreach ($values as $value)
		{
			$name = $value->Name;
			if ($name != 'ID')
			{
				$properties[] = $type::$name($value->Value);
			}
		}
		if ($this->ID->Value !== null)
		{
			$reflector = new ReflectionClass('Update');
			$query = $reflector->newInstanceArgs($properties);
			$query->Where($type::ID($this->ID));
		}
		else
		{
			$reflector = new ReflectionClass('Insert');
			$query = $reflector->newInstanceArgs($properties);
		}
		Database::ExecuteQuery($query->Compile());
	}
	
	public static function _($alias)
	{
		DomainObject::$last_alias = $alias;
	}
	
	public static function All()
	{
		return new ColumnReference(get_called_class());
	}
	
	protected final function AddProperty(DataObject $property)
	{
		if (!$this->properties)
		{
			$this->properties = array();
		}
		$this->properties[] = $property;
	}
	
	public static function GetDataStructure()
	{
		
	}
	
	public function __get($name)
	{
		foreach ($this->properties as $property)
		{
			if ($property->Name === $name)
			{
				return $property;
			}
		}
		
		throw new \BadMethodCallException('Unknown property: '.$name);
	}
	
	public function __set($name, $value)
	{
		foreach ($this->properties as $property)
		{
			if ($property->Name === $name)
			{
				return $property->SetValue($value);
			}
		}
		
		throw new \BadMethodCallException('Unknown property: '.$name);
	}
	
	public function HasProperty($name)
	{
		foreach ($this->properties as $property)
		{
			if ($property->Name === $name)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	public static function __callStatic($name, $args)
	{
		$class = get_called_class();
		$do = new $class();
		if ($do->HasProperty($name))
		{
			return new ColumnReference(get_called_class(), $do->$name, $args ? $args[0] : NULL);
		}
		$ops = Database::GetComparisonOperators();
		if (isset($ops[$name]))
		{
			return new ComparisonOperator($name, array_merge(array(new ColumnReference($class, $do->ID)), $args));
		}
		$class = get_class($this);
		$trace = debug_backtrace();
		$file = $trace[0]['file'];
		$line = $trace[0]['line'];
		trigger_error("Call to undefined method $class::$name() in $file on line $line", E_USER_ERROR);
	}
	
	public static function On(ComparisonOperator $condition = NULL)
	{
		return new Join(new ColumnReference(get_called_class()), $condition);
	}
	
	public static function IsA($obj, $check_string = FALSE)
	{
		if ($check_string === TRUE && is_string($obj) && class_exists($obj))
		{
			$obj = new $obj();
		}
		return $obj !== NULL && is_a($obj, get_called_class());
	}
	
	public function Load()
	{
		
	}
	
	public function LoadByID()
	{
		
	}
	
	//public function 
	
}
