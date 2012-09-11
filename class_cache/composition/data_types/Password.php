<?php Samara_Include('String', 'inc/primitive_types');

class Password extends String
{
	
	public function __construct($name, $value = NULL)
	{
		parent::__construct($name, 128, $value == null ? null : $value);
	}
	
	public function GetNativeType()
	{
		return 'CHAR';
	}
	
	public function SetValue($value)
	{
		return ($this->value = hash("sha512", $value));
	}
	
}

