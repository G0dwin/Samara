<?php Samara_Include('Database', 'inc');

abstract class DataObject {
	
	protected $value;
	protected $name;

	public function __construct($name = NULL, $value = NULL)
	{
		$this->name = $name;
		$this->SetValue($value);
	}
	
	public function GetValue()
	{
		return $this->ValueOf($this->value);
	}
	
	public abstract function GetNativeType();
	
	public final function __get($name)
	{
		switch ($name)
		{
			case 'Value':
				return $this->GetValue();
			case 'NativeValue':
				return $this->GetNativeValue();
			case 'NativeType':
				return $this->GetNativeType();
			case 'NativeName':
				return $this->GetNativeName();
			case 'Size':
				return $this->GetSize();
			case 'Name':
				return $this->GetName();
			case 'Properties':
				return $this->GetProperties();
			case 'Nullable':
				return $this->IsNullable();
			case 'AutoIncrement':
				return $this->DoesAutoIncrement();
			case 'PrimaryKey':
				return $this->IsPrimaryKey();
			case 'DefaultValue':
				return $this->GetDefaultValue();
		}
		throw new \BadMethodCallException('Unknown property: '.$name);
	}
	
	public final function __set($name, $data)
	{
		switch ($name)
		{
			case 'Value':
				return $this->SetValue($data);
			case 'RawValue':
			case 'Size':
				throw new \BadMethodCallException('Property `'.$name.'` is read-only');
		}
		
		throw new \BadMethodCallException('Unknown property: '.$name);
	}
	
	/*public abstract static function MachineName()
	{
		return Samara_ToUnderscoreCase(Samara_GetClassName(get_called_class()));
	}* /
	
	public static function FullName()
	{
		$class = get_called_class();
		$name = $class::MachineName();
		if (($size = $class::Size()) !== false)
		{
			$name .= '('.$size.')';
		}
		if (($properties = $class::Properties()) !== false)
		{
			$name .= ' '.implode(' ', $properties);
		}
		return $name;
	}*/
	
	public function ValueOf($value)
	{
		return $value;
	}
	
	public abstract function GetSize();
	
	public function GetProperties()
	{
		return array();
	}
	
	public function DefaultSize()
	{
		return 10;
	}
	
	public function GetName()
	{
		return $this->name;
	}
	
	public function FormatName()
	{
		return '`'.$this->GetNativeName().'`';
	}
	
	public function SetValue($value)
	{
		$this->value = $value;
		return $this;
	}
	
	public function GetNativeValue()
	{
		return $this->value;
	}
	
	public function FormatValue()
	{
		return $this->FormatAlternateValue($this->GetNativeValue());
	}

	public function FormatAlternateValue($value)
	{
		if ($value === null)
		{
			return Database::FormatNull();
		}
		return $this->FormatNonNullValue($value);
	}
	
	public function FormatNonNullValue($value)
	{
		return $value;
	}
	
	public function GetNativeName()
	{
		return Samara_ToUnderscoreCase($this->name);
	}
	
	public static function IsA($obj)
	{
		return is_a($obj, get_called_class());
	}
	
	public function IsNullable()
	{
		return false;
	}
	
	public function IsPrimaryKey()
	{
		return false;
	}
	
	public function DoesAutoIncrement()
	{
		return false;
	}
	
	public function GetDefaultValue()
	{
		return null;
	}
	
	public function CompileForCreate()
	{
		return Database::FormatTable($this->GetNativeName()).' '.$this->GetFullTypeName().($this->IsNullable() ? '' : ' NOT NULL').
			($this->CanHaveDefaultValue() ? ' DEFAULT '.$this->FormatAlternateValue($this->GetDefaultValue()) : '').
			($this->IsPrimaryKey() ? ' PRIMARY KEY' : ($this->IsUnique() ? '  UNIQUE' : '')).
			($this->DoesAutoIncrement() ? ' AUTO_INCREMENT' : '');
	}
	
	public function CanHaveDefaultValue()
	{
		return !($this->DoesAutoIncrement());
	}
	
	public function GetFullTypeName()
	{
		$properties = implode(' ', $this->GetProperties());
		return $this->GetNativeType().($this->GetSize() === NULL ?
					'' :
					'('.(is_array($this->GetSize()) ? implode(', ', $this->GetSize()) : $this->GetSize()).')').($properties ? ' '.$properties : '');
	}
	
	public function IsOutdated($column_info)
	{
		return (strcasecmp(str_replace(', ', ',', $column_info['Type']), str_replace(', ', ',', $this->GetFullTypeName())) !== 0) ||
				(($column_info['Null'] !== 'NO') !== $this->IsNullable()) ||
				(strcasecmp($column_info['Key'], $this->IsPrimaryKey() ? 'PRI' : ($this->IsUnique() ? 'UNI' : ''))) ||
				($this->CanHaveDefaultValue() && $column_info['Default'] !== $this->GetDefaultValue()) ||
				strcasecmp($column_info['Extra'], $this->GetExtra()) !== 0;
	}
	
	public function GetExtra()
	{
		return ($this->DoesAutoIncrement() ? 'AUTO_INCREMENT' : '');
	}
	
	public function IsUnique()
	{
		return false;
	}
	
	public function RequiresAlternateTable()
	{
		return false;
	}
	
}
