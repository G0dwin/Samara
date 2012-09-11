<?php Samara_Include('UserName', 'composition/data_types');
Samara_Include('Password', 'composition/data_types');
Samara_Include('DomainObject', 'inc');

class User extends DomainObject
{
	
	public function __construct($name = null, $password = null)
	{
		parent::__construct();
		if ($name)
		{
			$this->Name = $name;
		}
		if ($password)
		{
			$this->Password = $password;
		}
	}
	
	protected function Properties()
	{
		parent::Properties();
		$this->AddProperty(new UserName('Name'));
		$this->AddProperty(new Password('Password'));
	}
	
}