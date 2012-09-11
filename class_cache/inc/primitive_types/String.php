<?php Samara_Include('DataObject', 'inc');

class String extends DataObject {
	
	protected $size;
	
	public function __construct($name = NULL, $size = NULL, $value = NULL)
	{
		$this->size = $size;
		parent::__construct($name, $value);
	}
	
	public function GetNativeType()
	{
		return 'VARCHAR';
	}
	
	public function FormatNonNullValue($value)
	{
		return "'{$value}'";
	}
	
	public function GetSize()
	{
		return $this->size;
	}
	
	public function GetDefaultValue()
	{
		return $this->IsNullable() ? null : '';
	}
	
}