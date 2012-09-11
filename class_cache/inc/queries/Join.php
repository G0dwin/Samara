<?php Samara_Include('QueryPart', 'inc/queries');
Samara_Include('Where', 'inc/queries');

class Join extends Where
{
	protected $table;
	protected $is_inner_join;
	
	public function __construct(ColumnReference $table, $condition = NULL, $inner = TRUE)
	{
		$this->table = $table;
		$this->is_inner_join = $inner;
		if ($condition !== NULL)
		{
			parent::__construct(is_array($condition) ? $condition : array($condition));
		}
	}
	
	function Compile()
	{
		$sql = ($this->is_inner_join ? $this->InnerJoinFormat() : $this->OuterJoinFormat()).Database::FormatTable($this->table->GetTable(), $this->table->GetTableAlias());
		
		if ($this->condition)
		{
			$sql .= $this->OnFormat().$this->condition->Compile();
		}
		
		return $sql;
	}
	
	public function On()
	{
		$this->SetConditions(func_get_args());
	}
	
	public function GetTable()
	{
		return $this->table;
	}
	
	public function SetInner($inner)
	{
		$this->is_inner_join = $inner;
	}
	
	protected function InnerJoinFormat()
	{
		return ' JOIN ';
	}

	protected function OuterJoinFormat()
	{
		return ' LEFT JOIN ';
	}
	
	protected function OnFormat()
	{
		return ' ON ';
	}
	
}

