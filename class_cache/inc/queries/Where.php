<?php 

Samara_Include('QueryPart', 'inc/queries');

class Where extends QueryPart
{

	protected $condition;
	
	public function __construct($conditions)
	{
		$this->SetConditions($conditions);
	}
	
	protected function SetConditions($conditions)
	{
		$this->condition = NULL;
		foreach ($conditions as $condition)
		{
			if ($this->condition === NULL)
			{
				if (LogicalOperator::IsA($condition))
				{
					$this->condition = $condition;
					$this->condition->SetParent($this);
				}
				else if (ComparisonOperator::IsA($condition) && $condition->GetParent())
				{
					$this->condition = $condition->GetParent();
					$this->condition->SetParent($this);
				}
				else
				{
					$this->condition = new LogicalOperator(NULL, array($condition), $this);
				}
			}
			else
			{
				$this->condition->SetOperator('And');
				$this->condition->AddCondition($condition);
			}
		}
	}
	
	public function Compile($table_name_is_required = TRUE)
	{
		return $this->WhereFormat().(ColumnReference::IsA($this->condition) ? $this->condition->Compile($table_name_is_required) : $this->condition);
	}
	
	protected function WhereFormat()
	{
		return 'WHERE ';
	}
	
	public function _Or()
	{
		$expression = func_get_args();
		if (count($expression) == 1 && is_array($expression[0]))
		{
			$expression = $expression[0];
		}
		if ($this->condition->IsOr() || $this->condition->IsUndefined())
		{
			$this->condition->SetOperator('Or');
			$this->condition->AddCondition($expression);
		}
		else
		{
			$this->condition = $this->condition->NewParent('Or', $expression);
		}
		return $this;
	}
	
	public function _And()
	{
		$expression = func_get_args();
		if (count($expression) == 1 && is_array($expression[0]))
		{
			$expression = $expression[0];
		}
		if ($this->condition->IsAnd() || $this->condition->IsUndefined())
		{
			$this->condition->SetOperator('And');
			$this->condition->AddCondition($expression);
		}
		else
		{
			$this->condition = $this->condition->NewParent('And', $expression);
		}
		return $this;
	}
	
}

