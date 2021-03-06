<?php 

Samara_Include('QueryPart', 'inc/queries');

class ColumnReference extends QueryPart
{
	protected $domain_object;
	protected $domain_object_alias;
	protected $data_object;
	protected $data_object_alias;
	protected $descending;
	
	public function __construct($domain_type, $data_object = NULL, $data_object_alias = NULL)
	{
		$this->domain_object = is_string($domain_type) ? new $domain_type() : $domain_type;
		$this->data_object = $data_object;
		$this->data_object_alias = $data_object_alias;
	}
	
	public function Asc()
	{
		$this->descending = FALSE;
		return $this;
	}
	
	public function Desc()
	{
		$this->descending = TRUE;
		return $this;
	}
	
	public function Compile($table_name_is_required = TRUE, $alias_is_value = FALSE)
	{
		$col = $this->data_object === null ? Database::FormatAll() : ($alias_is_value ? Database::FormatSetExpression($this->data_object->NativeName, $this->data_object_alias) : Database::FormatColumn($this->data_object->NativeName, $this->data_object_alias));
		return $table_name_is_required ? Database::TableColumn(Database::FormatTable($this->GetTableName()), $col) : $col;
	}
	
	public function GetDomain($internal_column = 0)
	{
		return $this->domain_object;
	}
	
	public function GetTable($internal_column = 0)
	{
		return $this->domain_object->NativeName();
	}
	
	public function GetColumn()
	{
		return $this->data_object->NativeName;
	}
	
	public function HasTableAlias($internal_column = 0)
	{
		return $this->domain_object_alias !== NULL;
	}
	
	public function GetTableAlias($internal_column = 0)
	{
		return $this->domain_object_alias;
	}
	
	public function GetTableName($internal_column = 0)
	{
		return $this->domain_object_alias ? Samara_ToUnderscoreCase($this->domain_object_alias) : $this->GetTable();
	}
	
	public function __call($name, $args)
	{
		if (AggreateFunction::IsAggreateFunction($name))
		{
			return new AggreateFunction($name, $this, $args ? $args[0] : NULL);
		}
		else if (Operator::IsOperator($name))
		{
			return new Operator($name, array_merge(array($this), $args));
		}
		else if (ComparisonOperator::IsComparisonOperator($name))
		{
			return new ComparisonOperator($name, array_merge(array($this), $args));
		}
		return parent::__call($name, $args);
	}
	
	public function FormatDataObjectValue()
	{
		return $this->FormatValue($this->data_object->Value);
	}
	
	public function FormatValue($value)
	{
		$data_object = $this->GetDataObject();
		return DataObject::IsA($data_object) ? $data_object->FormatAlternateValue($value) : $value;
	}
	
	public function GetSetValue()
	{
		return $this->FormatValue($this->data_object_alias);
	}
	
	public function GetDataObject()
	{
		return $this->data_object;
	}
	
	public function InternalColumnCount()
	{
		return 1;
	}
	
	public function RequiresBrackets()
	{
		return FALSE;
	}
	
	public function GetIsDescending()
	{
		return $this->descending;
	}
	
}

Samara_Include('AggreateFunction', 'inc/queries');
Samara_Include('Operator', 'inc/queries');
Samara_Include('ComparisonOperator', 'inc/queries');
Samara_Include('DataObject', 'inc');
