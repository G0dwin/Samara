<?php 

Samara_Include('TableAlias', 'inc/queries');

class SubQuery extends TableAlias
{
	public function __construct($query, $alias)
	{
		$this->domain_object = $query;
		$this->domain_object_alias = $alias;
	}
	
	public function Compile()
	{
		return Database::AddBrackets($this->domain_object->Compile(FALSE, TRUE)).($this->domain_object_alias === NULL ? '' : Database::FormatAlias($this->domain_object_alias));
	}

	public function GetTable($internal_column = 0)
	{
		return 0;
	}
	
	public function GetTableAlias($internal_column = 0)
	{
		return $this->domain_object_alias;
	}
	
	public function GetTableName($internal_column = 0)
	{
		return 0;//$this->domain_object_alias ? Samara_ToUnderscoreCase($this->domain_object_alias) : 0;
	}
	
}

Samara_Include('Database', 'inc');
