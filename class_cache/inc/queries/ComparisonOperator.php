<?php Samara_Include('Operator', 'inc/queries');
Samara_Include('LogicalOperator', 'inc/queries');

class ComparisonOperator extends Operator
{
	protected $parent;
	
	public function GetOperators()
	{
		return Database::GetComparisonOperators();
	}
	
	public static function IsComparisonOperator($name)
	{
		$ops = Database::GetComparisonOperators();
		return isset($ops[$name]);
	}
	
	public function _And()
	{
		$conditions = func_get_args();
		if (count($conditions) == 1 && is_array($conditions[0]))
		{
			$conditions = $conditions[0];
		}
		if (!$this->parent)
		{
			$this->parent = new LogicalOperator('And', array($this), NULL);
		}
 		return $this->parent->_And($conditions);
	}
	
	public function _Or()
	{
		$conditions = func_get_args();
		if (count($conditions) == 1 && is_array($conditions[0]))
		{
			$conditions = $conditions[0];
		}
		if (!$this->parent)
		{
			$this->parent = new LogicalOperator('Or', array($this), NULL);
		}
		return $this->parent->_Or($conditions);
	}
	
	public function SetParent(LogicalOperator $parent)
	{
		$this->parent = $parent;
	}
	
	public function GetParent()
	{
		return $this->parent;
	}
	
}
