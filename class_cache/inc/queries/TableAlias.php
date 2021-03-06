<?php 

Samara_Include('ColumnReference', 'inc/queries');

class TableAlias extends ColumnReference
{
	public static function __callstatic($name, $args)
	{
		$class = $args[0];
		$column = is_string($class) ? new TableAlias($class) : $class;
		$column->domain_object_alias = $name;
		return $column;
	}
	
	public function __call($name, $args)
	{
		if (array_search($name, Database::GetComparisonOperators()))
		{
			return new JoinStatement(new ColumnReference(get_called_class()), $args[0]);
		}
		return parent::__call($name, $args);
	}
	
	public function On($condition)
	{
		return new JoinStatement($this, $condition);
	}
	
}

Samara_Include('QueryPart', 'inc/queries');
Samara_Include('DataObject', 'inc');
