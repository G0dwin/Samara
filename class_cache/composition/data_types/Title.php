<?php Samara_Include('String', 'inc/primitive_types');

class Title extends String
{

	public function __construct($name, $value = NULL)
	{
		parent::__construct($name, 128, $value);
	}
	
}

