<?php Samara_Include('ColumnReference', 'inc/queries');

class Operator extends ColumnReference
{
	protected $operator;
	protected $args;
	
	public function __construct($operator, $args, $alias = NULL)
	{
		$this->operator = $operator;
		$this->args = $args;
		$this->data_object_alias = $alias;
	}
	
	public function Compile($table_name_is_required = TRUE)
	{
		$ops = $this->GetOperators();
		$compiled = array();
		for ($i = 0; $i < count($this->args); $i++)
		{
			$compiled[] = $this->CompileArg($i, $table_name_is_required);
		}
		return Database::Format
			(
					$ops[$this->operator],
					$compiled
			).($this->data_object_alias ? Database::FormatAlias($this->data_object_alias) : '');
	}
	
	public function CompileArg($arg, $table_name_is_required = TRUE)
	{
		$thisArg = $this->args[$arg];
		if (ColumnReference::IsA($thisArg))
		{
			$result = $thisArg->Compile($table_name_is_required);
			if ($thisArg->RequiresBrackets())
			{
				$result = Database::AddBrackets($result);
			}
			return $result;
		}
		$otherArg = NULL;
		for ($i = 0; $otherArg === NULL && $i < count($this->args); $i++)
		{
			if ($i != $arg && ColumnReference::IsA($this->args[$i]))
			{
				return $this->args[$i]->FormatValue($thisArg);
			}
		}
		return $thisArg;
	}
	
	public function GetOperators()
	{
		return Database::GetOperators();
	}
	
	public static function IsOperator($name)
	{
		$ops = Database::GetOperators();
		return isset($ops[$name]);
	}
	
	public function GetTable($internal_column = 0)
	{
		return ColumnReference::IsA($this->args[$internal_column]) ? $this->args[$internal_column]->GetTable() : 0;
	}
	
	public function GetTableAlias($internal_column = 0)
	{
		return ColumnReference::IsA($this->args[$internal_column]) ? $this->args[$internal_column]->GetTableAlias() : 0;
	}
	
	public function GetTableName($internal_column = 0)
	{
		return ColumnReference::IsA($this->args[$internal_column]) ? $this->args[$internal_column]->GetTableName() : 0;
	}
	
	public function InternalColumnCount()
	{
		return count($this->args);
	}
	
	public function RequiresBrackets()
	{
		return TRUE;
	}
	
	public function GetDataObject()
	{
		foreach ($this->args as $arg)
		{
			if (ColumnReference::IsA($arg))
			{
				return $arg->GetDataObject();
			}
		}	
		return $this->args[0];
	}
	
}
