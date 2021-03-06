<?php 

Samara_Include('Query', 'inc/queries');

class SelectQuery extends Query
{
	protected $columns;
	protected $where;
	protected $joins;
	protected $limit;
	protected $limit_start;
	protected $group_by;
	protected $group_by_with_rollup;
	protected $order_by;
	protected $having;
	
	public function __construct()
	{
		$this->columns = func_get_args();
	}

	public function __call($name, $args)
	{
		$ops = Database::GetComparisonOperators();
		if (isset($ops[$name]))
		{
			return call_user_func_array(array($this->_As(NULL), $name), $args);
		}
		return parent::__call($name, $args);
	}
	
	public function Where()
	{
		$conditions = func_get_args();
		$this->where = new WhereStatement($conditions);
		return $this;
	}
	
 	public function Compile($complete = TRUE, $table_names_required = FALSE)
 	{
 		$sql = 'SELECT ';
 		$cols = array();
 		$tables = array();
 		foreach ($this->columns as $col)
 		{
 			if (is_numeric($col))
 			{
 				if (!isset($cols[0]))
 				{
 					$cols[0] = array();
 				}
 				$cols[0][] = $col;
 			}
 			else if ($col === null)
 			{
 			 	if (!isset($cols[0]))
 				{
 					$cols[0] = array();
 				}
 				$cols[0][] = Database::FormatNull();
 			}
 			else if (DomainObject::IsA($col, TRUE))
 			{
 				$all = new ColumnReference($col);
 				//$tname = Samara_GetClassName($col);
 				$tname = $col;
 				if (!isset($cols[$tname]))
 				{
 					$cols[$tname] = array();
 				}
 				$cols[$tname][] = $all;
 				$tables[] = Database::FormatTable($all->GetTable(), $all->GetTableAlias());
 			}
 			else if (ColumnReference::IsA($col))
 			{
 				for ($i = 0; $i < $col->InternalColumnCount(); $i++)
 				{
 					$tname = $col->GetTableName($i); // the alias or actual name of the table
 					if (!isset($cols[$tname]) && $tname !== 0)
 					{
 						$cols[$tname] = array();
 					}
 					if ($i < 1)
 					{
 						$cols[$tname][] = $col;
 					}
 					$table = Database::FormatTable($col->GetTable($i), $col->GetTableAlias($i));
 					if ($tname !== 0 && array_search($table, $tables) === FALSE)
 					{
 						$tables[] = $table;
 					}
 				}
 			}
 			else
 			{
 				if (!isset($cols[0]))
 				{
 					$cols[0] = array();
 				}
 				$cols[0][] = Database::FormatString($col);
 			}
 		}
 		
 	 	$joins = '';
 	 	$join_tables = array();
 		if ($this->joins)
 		{
 			foreach ($this->joins as $join)
	 		{
	 			$joins .= $join->Compile();
	 			$col = $join->GetTable();
	 			$table = Database::FormatTable($col->GetTable($i), $col->GetTableAlias($i));
	 			if (array_search($table, $tables) === FALSE)
	 			{
	 				$tables[] = $table;
	 			}
	 			$join_tables[] = $table;
	 		}
 		}
 		
 		$table_name_is_required = $table_names_required || (count($tables) > 1);
 		
 		$columns = array();
 		foreach ($cols as $table => $table_columns)
 		{
 			foreach ($table_columns as $column)
 			{
 				$columns[] = ColumnReference::IsA($column) ? $column->Compile($table_name_is_required) : $column;
 			}
 		}
 		
 		$sql .= implode(', ', $columns);
 			
 		$from = NULL;
 		
 		foreach ($tables as $table)
 		{
 			if (array_search($table, $join_tables) === FALSE)
 			{
 				if ($from === NULL)
 				{
 					$from = ' FROM '.$table;
 				}
 				else
 				{
 					$from .= ', '.$table;
 				}
 			}
 		}
 		
 		$sql .= $from;
 		
 		$sql .= $joins;
 		
 		if ($this->where)
 		{
 			$sql .= ' '.$this->where->Compile($table_name_is_required);
 		}
 		
 	 	if ($this->group_by)
 		{
 			$gb = NULL;
	 		foreach ($this->group_by as $group)
	 		{
	 			if (SelectQuery::IsA($group))
	 			{
	 				$group = $group->As(NULL);
	 			}
	 			$compile = $group;
	 			if (ColumnReference::IsA($compile))
	 			{
	 				$desc = $compile->GetIsDescending();
	 				$compile = $compile->Compile($table_name_is_required);
	 				if ($desc !== NULL)
	 				{
	 					$compile .= $desc ? ' DESC' : ' ASC';
	 				}
	 			}
 				if ($gb === NULL)
 				{
 					$gb = ' GROUP BY '.$compile;
 				}
 				else
 				{
 					$gb .= ', '.$compile;
 				}
	 		}
	 		$sql .= $gb;
	 		if ($this->group_by_with_rollup)
	 		{
	 			$sql .= ' WITH ROLLUP';
	 		}
 		}
 		
 	 	if ($this->having)
 		{
 			$sql .= ' '.$this->having->Compile($table_name_is_required);
 		}
 		
 		if ($this->order_by)
 		{
 			$ob = NULL;
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
	 				$compile = $compile->Compile($table_name_is_required);
	 				if ($desc !== NULL)
	 				{
	 					$compile .= $desc ? ' DESC' : ' ASC';
	 				}
	 			}
 				if ($ob === NULL)
 				{
 					$ob = ' ORDER BY '.$compile;
 				}
 				else
 				{
 					$ob .= ', '.$compile;
 				}
	 		}
	 		$sql .= $ob;
 		}
 		
 		if ($this->limit !== NULL)
 		{
 			$sql .= ' LIMIT '.($this->limit_start === NULL ? '' : $this->limit_start.', ').$this->limit;
 		}
 		
 		return $complete ? Database::CompleteQuery($sql) : $sql;
 	}
 	
 	public function _Or($expression)
 	{
 		if ($this->having)
 		{
 			$this->having->_Or($expression);
 		}
 		else
 		{
 			$this->where->_Or($expression);
 		}
 		return $this;
 	}
	
 	public function _And($expression)
 	{
 	 	if ($this->having)
 		{
 			$this->having->_And($expression);
 		}
 		else
 		{
 			$this->where->_And($expression);
 		}
 		return $this;
 	}
 	
 	public function Join($stmt)
 	{
 		return $this->CreateJoin($stmt);
 	}
 	
 	public function LeftJoin($stmt)
 	{
 		return $this->CreateJoin($stmt, FALSE);
 	}
 	
 	public function CreateJoin($stmt, $inner = TRUE)
 	{
 		if (DomainObject::IsA(is_string($stmt) ? new $stmt() : $stmt))
 		{
 			//$property = Samara_GetClassName(is_string($stmt) ? $stmt : get_class($stmt));
 			$property = is_string($stmt) ? $stmt : get_class($stmt);
 			foreach ($this->columns as $column)
 			{
 				$do = $column->GetDomain();
 				if ($do->HasProperty($property))
 				{
 					$col = call_user_func_array(array(get_class($do), $property), array());
 					if ($column->HasTableAlias())
 					{
 						$alias = $column->GetTableAlias();
 						//$col = call_user_func_array(Samara_GetFullClassName(TableAlias).'::'.$alias, array($col));
 						$col = call_user_func_array(TableAlias.'::'.$alias, array($col));
 					}
 					return $this->CreateCrossJoin($stmt::On($stmt::Equals($col)), $inner);
 				}
 			}
 		}
 		return $this->CreateCrossJoin($stmt, $inner);
 	}

 	public function CrossJoin($stmt)
 	{
 		return $this->CreateCrossJoin($stmt);
 	}
 	
 	public function CreateCrossJoin($stmt, $inner = TRUE)
 	{
 		if (!JoinStatement::IsA($stmt))
 		{
 			$table = $stmt;
 			if (!ColumnReference::IsA($table))
 			{
 				$table = new ColumnReference($table);
 			}
 			$stmt = new JoinStatement($table);
 		}
 		if (!$this->joins)
 		{
 			$this->joins = array();
 		}
 		$stmt->SetInner($inner);
 		$this->joins[] = $stmt;
 		return $this;
 	}
 	
 	public function Limit($start_or_limit, $limit = NULL)
 	{
 		if ($limit === NULL)
 		{
 			$this->limit = $start_or_limit;
 			return $this;
 		}
 		$this->limit = $limit;
 		$this->limit_start = $start_or_limit;
 		return $this;
 	}
 	
 	public function _As($alias)
 	{
 		return new SubQuery($this, $alias);
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
 	
 	public function GroupBy()
 	{
 		$this->group_by = func_get_args();
 		return $this;
 	}

 	public function GroupByWithRollup()
 	{
 		$this->group_by_with_rollup = TRUE;
 		$this->group_by = func_get_args();
 		return $this;
 	}
 	
 	public function Having()
 	{
 		$conditions = func_get_args();
 		$this->having = new HavingStatement($conditions);
 		return $this;
 	}
}

Samara_Include('Database', 'inc');
Samara_Include('ColumnReference', 'inc/queries');
Samara_Include('TableAlias', 'inc/queries');
Samara_Include('WhereStatement', 'inc/queries');
Samara_Include('HavingStatement', 'inc/queries');
Samara_Include('SubQuery', 'inc/queries');
Samara_Include('DomainObject', 'inc');
