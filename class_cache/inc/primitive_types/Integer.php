<?php Samara_Include('DataObject', 'inc');

class Integer extends DataObject {
	
	public function __construct($name = NULL, $value = NULL)
	{
		parent::__construct($name, $value);
	}
	
	public function GetNativeType()
	{
		return 'INT';
	}
	
	public function GetSize()
	{
		return 4;
	}
	
	public function GetProperties()
	{
		$properties = parent::GetProperties();
		if ($this->IsUnsigned())
		{
			$properties[] = 'UNSIGNED';
		}
		if ($this->IsZeroFill())
		{
			$properties[] = 'ZEROFILL';
		}
		return $properties;
	}
	
	public function IsUnsigned()
	{
		return false;
	}
	
	public function IsZeroFill()
	{
		return false;
	}

	public function GetDefaultValue()
	{
		return $this->IsNullable() ? null : 0;
	}
	
}