<?php 

Samara_Include('Query', 'inc/queries');
Samara_Include('SelectQuery', 'inc/queries');

class Update extends Query
{
	protected $values;
	protected $where;
	protected $order_by;
	protected $limit;
	
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
		$where = NULL;
		$order_by = NULL;
		$limit = NULL;
	 	
	 	if ($this->where)
 		{
 			$where = ' '.$this->where->Compile(FALSE);
 		}
 		
		if ($this->order_by)
 		{
	 		foreach ($this->order_by as $order)
	 		{
	 			if (SelectQuery::IsA($order))
	 			{
	 				$order = $order->As(NULL);
	 			}
	 			$compile = $order;
	 			if (ColumnReference::IsA($compile))
	 			{
	 				$desc = $compile->GetIsDescending();
	 				$compile = $compile->Compile(FALSE);
	 				if ($desc !== NULL)
	 				{
	 					$compile .= $desc ? ' DESC' : ' ASC';
	 				}
	 			}
 				if ($order_by === NULL)
 				{
 					$order_by = ' ORDER BY '.$compile;
 				}
 				else
 				{
 					$order_by .= ', '.$compile;
 				}
	 		}
 		}
 		
 		if ($this->limit !== NULL)
 		{
 			$limit = ' LIMIT '.$this->limit;
 		}
 		
		return Database::CompleteQuery($this->CompileHead().($where ?: '').($order_by ?: '').($limit ?: ''));		
	}
	
	function CompileHead()
	{
		$columns = NULL;
		foreach ($this->values AS $column)
		{
			/*if (!is_object($column))
			{
				print_r($column);
				die('['.$column.']');
			}*/
			$columns = ($columns === NULL ? '' : $columns.', ').Database::FormatSetExpression($column->GetColumn(), $column->GetSetValue());
		}
		return 'UPDATE '.Database::FormatTable($this->values[0]->GetTable()).' SET '.$columns;
	}
	
	public function Where()
	{
		$conditions = func_get_args();
		$this->where = new Where($conditions);
		return $this;
	}
	
 	public function _Or($expression)
 	{
		$this->where->_Or($expression);
 		return $this;
 	}
	
 	public function _And($expression)
 	{
		$this->where->_And($expression);
 		return $this;
 	}
 	
 	public function Limit($limit)
 	{
 		$this->limit = $limit;
 		return $this;
 	}
 	
 	public function OrderBy()
 	{
 		$this->order_by = func_get_args();
 		return $this;
 	}
 	
 	public function Asc()
 	{
 		return $this->_As(NULL)->Asc();
 	}
 	
 	public function Desc()
 	{
 		return $this->_As(NULL)->Desc();
 	}
 	
}
