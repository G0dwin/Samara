<?php 

Samara_Include('Renderable', 'inc');
Samara_Include('SamaraBase', 'inc');

abstract class DomainObject extends SamaraBase implements Renderable {
	
	protected $properties;
	protected $last_alias;
	
	public function __construct()
	{
		$this->Properties();
		/*$args = func_get_args();
		if ($args)
		{
			call_user_func_array(array($this, 'Load'), $args);
		}*/
	}
		
	public static function NativeName()
	{
		//return Samara_ToUnderscoreCase(Samara_GetClassName(get_called_class()));
		return Samara_ToUnderscoreCase(get_called_class());
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
			if ($name != 'ID' && $value->IsColumn())
			{
				$value->PreSave();
				$properties[] = $type::$name($value->Value);
			}
		}
		if ($this->ID->Value !== null)
		{
			$reflector = new ReflectionClass('UpdateQuery');
			$query = $reflector->newInstanceArgs($properties);
			$query->Where($type::ID()->Equals($this->ID->Value));
			Database::ExecuteQuery($query->Compile());
			/*if (!Database::ExecuteQuery(Database::Update($properties)->Where($type::ID()->Equals($this->ID->Value))->Compile()))
			{
				return false;
			}*/
		}
		else
		{
			$reflector = new ReflectionClass('InsertQuery');
			$query = $reflector->newInstanceArgs($properties);
			Database::ExecuteQuery($query->Compile());
			/*if (!Database::ExecuteQuery(Database::Insert($properties)->Compile()))
			{
				return false;
			}*/
			$this->ID->Value = Database::GetLatestID();
		}
		//$id = Database::GetLatestID();//Database::ExecuteQuery($query->Compile());
		foreach ($values as $value)
		{
			$value->PostSave();
		}
		return $this->ID->Value;
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
		return $this->properties[] = $property;
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
		
		throw new BadMethodCallException('Unknown property: '.$name);
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
		
		throw new BadMethodCallException('Unknown property: '.$name);
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
		if (substr($name, 0, 6) === 'FindBy')
		{
			return call_user_func_array(array($class, 'FindBy'), array_merge(array(preg_replace('/^(FindBy)(.*)$/', '$2', $name)), $args));
		}
		if (substr($name, 0, 9) === 'FindOneBy')
		{
			return call_user_func_array(array($class, 'FindOneBy'), array_merge(array(preg_replace('/^(FindOneBy)(.*)$/', '$2', $name)), $args));
		}

		$trace = debug_backtrace();
		$file = $trace[0]['file'];
		$line = $trace[0]['line'];
		trigger_error("Call to undefined method $class::$name() in $file on line $line", E_USER_ERROR);
	}
	
	protected static function FindBy($property, $value)
	{
		$class = get_called_class();
		$results = Database::ExecuteQuery(Database::Select($class::All())->Where($class::$property()->Equals($value)));
		$objects = array();
		foreach ($results as $result)
		{
			$objects[] = $class::fromDBResult($result);
		}
		return $objects;
	}
	
	protected static function FindOneBy($property, $value)
	{
		$class = get_called_class();
		$result = Database::ExecuteQuery(Database::Select($class::All())->Where($class::$property()->Equals($value))->Limit(1));
		$result = $result[0];
		return $class::fromDBResult($result);
	}
	
	public static function Get($id)
	{
		$class = get_called_class();
		return $class::FindOneByID('ID', $id);
	}
	
	public static function CountAll()
	{
		$class = get_called_class();
		$results = Database::ExecuteQuery(Database::Select($class::All()->Count('count')));
		return $results[0]['count'];
	}

	public static function GetAll($load = true)
	{
		$class = get_called_class();
		$results = Database::ExecuteQuery(Database::Select($class::All()));
		$objects = array();
		foreach ($results as $result)
		{
			$objects[] = $load ? $class::fromDBResult($result) : $result['id'];
		}
		return $objects;
	}
	
	public static function CreateFromQuery($query)
	{
		$class = get_called_class();
		$results = Database::ExecuteQuery($query);
		$objects = array();
		foreach ($results as $result)
		{
			$objects[] = $class::fromDBResult($result);
		}
		return $objects;
	}
	
	protected static function fromDBResult($result)
	{
		$class = get_called_class();
		$object = new $class();
		foreach ($object->GetProperties() as $property)
		{
			$property->PreLoad();
			if ($property->IsColumn())
			{
				$property_name = $property->Name;
				$object->$property_name = $result[$property->NativeName];
			}
			$property->PostLoad();
		}
		return $object;
	}
	
	public static function On(ComparisonOperator $condition = NULL)
	{
		return new JoinStatement(new ColumnReference(get_called_class()), $condition);
	}
	
	public static function IsA($obj, $check_string = FALSE)
	{
		if ($check_string === TRUE && is_string($obj) && class_exists($obj))
		{
			$obj = new $obj();
		}
		return $obj !== NULL && is_a($obj, get_called_class());
	}
	
	public function RenderForm($type = null)
	{
		$xml = '<form controller="'.Samara_ToUnderscoreCase(get_class($this)).'" action="save'.($type === null ? '' : '-'.$type).'" id="save'.($type === null ? '' : '-'.$type).'" title="Save"'.($type === null ? '' : ' type="'.$type.'"').'>';
		foreach ($this->GetProperties() as $property)
		{
			$xml .= $property->RenderInput();
		}
		$xml .= '</form>';
		return $xml;
	}
	
	public function Render($view = null)
	{
		$xml = '<object type="'.Samara_ToUnderscoreCase(get_class($this)).'" display-name="'.((string)$this).'" id="'.$this->ID->Value.'">';
		//if (!$form_view)
		//{
			foreach ($this->GetProperties() as $property)
			{
				$xml .= $property->Render($view);
			}
		//}
		$xml .= '</object>';
		return $xml;
	}
	
	public function PropertyOfType($type)
	{
		//$type = Samara_GetFullClassName($type);
		foreach ($this->GetProperties() as $property)
		{
			if (is_a($property, $type))
			{
				return $property;
			}
		}
		return null;
	}
	
	public function __toString()
	{
		$property = $this->PropertyOfType('Title');
		if ($property)
		{
			return $property->Value ?: '';
		}
		return get_class($this).' '.($this->ID->Value ?: '(new)');
	}
	
	public function SetFromFormResult($prefix = null, $suffix = null)
	{
		foreach ($this->GetProperties() as $property)
		{
			$property_name = $property->Name;
			if ($this->$property_name->SetFromFormResult($prefix, $suffix) === false)
			{
				//echo get_class($this).' property: '.$property_name."\n";
				return false;
			}
		}
	}
	
}

Samara_Include('DataObject', 'inc');
Samara_Include('ColumnReference', 'inc/queries');
Samara_Include('JoinStatement', 'inc/queries');
Samara_Include('InsertQuery', 'inc/queries');
Samara_Include('UpdateQuery', 'inc/queries');
Samara_Include('Identifier', 'inc/data_types');
