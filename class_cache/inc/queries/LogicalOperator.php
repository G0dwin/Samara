<?php Samara_Include('Operator', 'inc/queries');

class LogicalOperator extends Operator
{
	
	protected $parent;
	
	public function __construct($operator, $args, $parent)
	{
		$this->args = $args;
		$this->operator = $operator;
		$this->parent = $parent;
		
		foreach ($this->args as $arg)
		{
			if (ComparisonOperator::IsA($arg) || LogicalOperator::IsA($arg))
			{
				$arg->SetParent($this);
			}
		}
	}

	public function GetOperators()
	{
		return Database::GetComparisonOperators();
	}
	
	public static function IsComparisonOperator($name)
	{
		$ops = Database::GetComparisonOperators();
		return isset($ops[$name]);
	}
	
	public function IsAnd()
	{
		return $this->operator == 'And';
	}
	
	public function IsOr()
	{
		return $this->operator == 'Or';
	}
	
	public function IsUndefined()
	{
		return $this->operator === NULL;
	}
	
	public function Compile($table_name_is_required = TRUE)
	{
		$sql = NULL;
		$format = "Format{$this->operator}Expression";
		foreach ($this->args as $arg)
		{
			$compiled = ColumnReference::IsA($arg) ? $arg->Compile($table_name_is_required) : $arg;
			$sql = $sql === NULL ? $compiled : Database::$format($sql, $compiled);
		}
		if (LogicalOperator::IsA($this->parent))
		{
			$sql = Database::AddBrackets($sql);
		}
		return $sql;
	}
	
	public function SetOperator($operator)
	{
		$this->operator = $operator;
	}
	
	public function AddCondition($condition)
	{
		if (is_array($condition))
		{
			foreach ($condition as $c)
			{
				$this->AddCondition($c);
			}
			return;
		}
		if (ComparisonOperator::IsA($condition) || LogicalOperator::IsA($condition))
		{
			$condition->SetParent($this);
		}
		$this->args[] = $condition;
	}
	
	public function NewParent($operator, $expression)
	{
		if (ComparisonOperator::IsA($expression) || LogicalOperator::IsA($expression))
		{
			$expression->SetParent($this);
		}
		$op = new LogicalOperator($operator, array_merge(array($this), is_array($expression) ? $expression : array($expression)), $this->parent);
		$this->parent = $op;
		return $op;
	}
	
	public function SetParent($parent)
	{
		$this->parent = $parent;
	}
	
	public function _And()
	{
		$conditions = func_get_args();
		if (count($conditions) == 1 && is_array($conditions[0]))
		{
			$conditions = $conditions[0];
		}
		if ($this->IsAnd() || $this->IsUndefined())
		{
			$this->operator = 'And';
			$this->AddCondition($conditions);
		}
		else
		{
			$this->parent->_And($conditions);
		}
		return $this;
	}
	
	public function _Or()
	{
		$conditions = func_get_args();
		if (count($conditions) == 1 && is_array($conditions[0]))
		{
			$conditions = $conditions[0];
		}
		if ($this->IsOr() || $this->IsUndefined())
		{
			$this->operator = 'Or';
			$this->AddCondition($conditions);
		}
		else
		{
			$this->parent->_Or($conditions);
		}
		return $this;
	}
	
}
