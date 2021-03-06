<?php 

Samara_Include('Query', 'inc/queries');
Samara_Include('Database', 'inc');

class Insert extends Query
{
	protected $values;
	
	public function __construct()
	{
		$this->values = func_get_args();
	}
	
	public function AddColumns()
	{
		$this->values = array_merge($this->values ?: array(), func_get_args());
		return $this;
	}
	
	public function Compile()
	{
		$columns = NULL;
		$values = NULL;
		foreach ($this->values AS $column)
		{
			$columns = ($columns === NULL ? '' : $columns.', ').Database::FormatColumn($column->GetColumn());
			$values = ($values === NULL ? '' : $values.', ').$column->GetSetValue();
		}
		return Database::CompleteQuery('INSERT INTO '.Database::FormatTable($this->values[0]->GetTable()).' ('.$columns.') VALUES ('.$values.')');		
	}

}

