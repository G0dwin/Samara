<?php 

Samara_Include('Database', 'inc');
Samara_Include('Controller', 'inc');

abstract class DataObject extends SamaraBase
{
	
	protected $value;
	protected $name;
	protected $is_nullable;

	public function __construct($name = NULL, $value = NULL)
	{
		$this->name = $name;
		$this->SetValue($value);
		$this->is_nullable = null;
	}
	
	public function GetValue()
	{
		return $this->ValueOf($this->value);
	}
	
	public function MakeNullable()
	{
		if ($this->is_nullable === null)
		{
			$this->is_nullable = true;
		}
	}
	
	public abstract function GetNativeType();
	
	public final function __get($name)
	{
		switch ($name)
		{
			case 'Value':
				return $this->GetValue();
			case 'NativeValue':
				return $this->GetNativeValue();
			case 'NativeType':
				return $this->GetNativeType();
			case 'NativeName':
				return $this->GetNativeName();
			case 'Size':
				return $this->GetSize();
			case 'Name':
				return $this->GetName();
			case 'Properties':
				return $this->GetProperties();
			case 'Nullable':
				return $this->IsNullable();
			case 'AutoIncrement':
				return $this->DoesAutoIncrement();
			case 'PrimaryKey':
				return $this->IsPrimaryKey();
			case 'DefaultValue':
				return $this->GetDefaultValue();
		}
		throw new \BadMethodCallException('Unknown property: '.$name);
	}
	
	public final function __set($name, $data)
	{
		switch ($name)
		{
			case 'Value':
				return $this->SetValue($data);
			case 'RawValue':
			case 'Size':
				throw new \BadMethodCallException('Property `'.$name.'` is read-only');
		}
		
		throw new \BadMethodCallException('Unknown property: '.$name);
	}
	
	/*public abstract static function MachineName()
	{
		return Samara_ToUnderscoreCase(Samara_GetClassName(get_called_class()));
	}* /
	
	public static function FullName()
	{
		$class = get_called_class();
		$name = $class::MachineName();
		if (($size = $class::Size()) !== false)
		{
			$name .= '('.$size.')';
		}
		if (($properties = $class::Properties()) !== false)
		{
			$name .= ' '.implode(' ', $properties);
		}
		return $name;
	}*/
	
	public function ValueOf($value)
	{
		return $value;
	}
	
	public abstract function GetSize();
	
	public function GetProperties()
	{
		return array();
	}
	
	public function DefaultSize()
	{
		return 10;
	}
	
	public function GetName()
	{
		return $this->name;
	}
	
	public function FormatName()
	{
		return '`'.$this->GetNativeName().'`';
	}
	
	public function SetValue($value)
	{
		$this->value = $value;
		return $this;
	}
	
	public function GetNativeValue()
	{
		return $this->value;
	}
	
	public function FormatValue()
	{
		return $this->FormatAlternateValue($this->GetNativeValue());
	}

	public function FormatAlternateValue($value)
	{
		if ($value === null)
		{
			return Database::FormatNull();
		}
		return $this->FormatNonNullValue($value);
	}
	
	public function FormatNonNullValue($value)
	{
		return $value;
	}
	
	public function GetNativeName()
	{
		return Samara_ToUnderscoreCase($this->name);
	}
	
	public static function IsA($obj)
	{
		return is_a($obj, get_called_class());
	}
	
	public function IsNullable()
	{
		return $this->is_nullable ?: false;
	}
	
	public function IsPrimaryKey()
	{
		return false;
	}
	
	public function DoesAutoIncrement()
	{
		return false;
	}
	
	public function GetDefaultValue()
	{
		return null;
	}
	
	public function CompileForCreate($tables)
	{
		return Database::FormatTable($this->GetNativeName()).' '.$this->GetFullTypeName().($this->IsNullable() ? '' : ' NOT NULL').
			($this->CanHaveDefaultValue() ? ' DEFAULT '.$this->FormatAlternateValue($this->GetDefaultValue()) : '').
			($this->IsPrimaryKey() ? ' PRIMARY KEY' : ($this->IsUnique() ? '  UNIQUE' : '')).
			($this->DoesAutoIncrement() ? ' AUTO_INCREMENT' : '');
	}
	
	public function CompileForPreCreate($tables, $columns)
	{
		return null;
	}
	
	public function CanHaveDefaultValue()
	{
		return !($this->DoesAutoIncrement());
	}
	
	public function GetFullTypeName()
	{
		$properties = implode(' ', $this->GetProperties());
		return $this->GetNativeType().($this->GetSize() === NULL ?
					'' :
					'('.(is_array($this->GetSize()) ? implode(', ', $this->GetSize()) : $this->GetSize()).')').($properties ? ' '.$properties : '');
	}
	
	public function IsOutdated($column_info)
	{
		/*$deb = (strcasecmp(str_replace(', ', ',', $column_info['Type']), str_replace(', ', ',', $this->GetFullTypeName())) !== 0) ? ('strcasecmp('.str_replace(', ', ',', $column_info['Type']).', '.str_replace(', ', ',', $this->GetFullTypeName()).') { '.strcasecmp(str_replace(', ', ',', $column_info['Type']), str_replace(', ', ',', $this->GetFullTypeName())).' }'."\n") : '';
		$deb .= ((strcasecmp($column_info['Null'], 'NO') === 0) !== !$this->IsNullable()) ? ('(strcasecmp('.$column_info['Null'].', \'NO\') === 0) !== '.!$this->IsNullable().' { '.strcasecmp($column_info['Null'], 'NO').' | '.!$this->IsNullable().' }'."\n") : '';
		$deb .= (strcasecmp($column_info['Key'], $this->IsPrimaryKey() ? 'PRI' : ($this->IsUnique() ? 'UNI' : ''))) ? ('strcasecmp('.$column_info['Key'].', '.$this->IsPrimaryKey().' ? \'PRI\' : ('.$this->IsUnique().' ? \'UNI\' : \'\')) !== 0 { '.(strcasecmp($column_info['Key'], $this->IsPrimaryKey() ? 'PRI' : ($this->IsUnique() ? 'UNI' : ''))).' }'."\n") : '';
		$deb .= ($this->CanHaveDefaultValue() && $column_info['Default'] !== $this->GetDefaultValue()) ?
			(
					$this->CanHaveDefaultValue().' && '.$column_info['Default'].' !== '.$this->GetDefaultValue().
					' { '.($this->CanHaveDefaultValue() && $column_info['Default'] !== $this->GetDefaultValue()).' | '.($column_info['Default'] != $this->GetDefaultValue()).' }'."\n"
			) : '';
		$deb .= (strcasecmp($column_info['Extra'], $this->GetExtra()) !== 0) ? ('strcasecmp('.$column_info['Extra'].', '.$this->GetExtra().') !== 0 { '.strcasecmp($column_info['Extra'], $this->GetExtra()).' }'."\n") : '';
		*/
		$result = (strcasecmp(str_replace(', ', ',', $column_info['Type']), str_replace(', ', ',', $this->GetFullTypeName())) !== 0) ||
				((strcasecmp($column_info['Null'], 'NO') === 0) !== !$this->IsNullable()) ||
				(strcasecmp($column_info['Key'], $this->IsPrimaryKey() ? 'PRI' : ($this->IsUnique() ? 'UNI' : ''))) !== 0 ||
				($this->CanHaveDefaultValue() && $column_info['Default'] != $this->GetDefaultValue()) ||
				strcasecmp($column_info['Extra'], $this->GetExtra()) !== 0;
		/*if ($result && $deb)
		{
			echo $deb;
		}*/
		return $result;
	}
	
	public function GetExtra()
	{
		return ($this->DoesAutoIncrement() ? 'AUTO_INCREMENT' : '');
	}
	
	public function IsUnique()
	{
		return false;
	}
	
	public function RequiresAlternateTable()
	{
		return false;
	}
	
	public function getInheritanceList($underscore_case = false)
	{
		$ancestor = get_class($this);
		$ancestors = array();
		while ($ancestor)
		{
			$ancestors[] = ($underscore_case ? Samara_ToUnderscoreCase($ancestor) : $ancestor);
			$ancestor = get_parent_class($ancestor);
		}
		return $ancestors;
	}
	
	public function RenderInput()
	{
		$types = array();
		$i = 0;
		foreach ($this->getInheritanceList(true) as $ancestor)
		{
			$types[] = 'type-'.($i++).'="'.$ancestor.'"';
		}
		$underscore = Samara_ToUnderscoreCase($this->GetName());
		return '<control '.implode(' ', $types).' param="'.$underscore.'" label="'.$this->GetName().'">'.$this->GetValue().'</control>';
	}
	
	public function Render($forms_view = false)
	{
		$types = array();
		$i = 0;
		foreach ($this->getInheritanceList(true) as $ancestor)
		{
			$types[] = 'type-'.($i++).'="'.$ancestor.'"';
		}
		$underscore = Samara_ToUnderscoreCase($this->GetName());
		return '<field '.implode(' ', $types).' param="'.$underscore.'" value="'.str_replace("\n", '&amp;&#35;xA;', $this->GetValue()).'" label="'.$this->GetName().'">'.str_replace('\'', '\\\'', $this->GetValue()).'</field>';
	}
	
	public function SetFromFormResult($prefix = null, $suffix = null)
	{
		$param = ($prefix ?: '').Samara_ToUnderscoreCase($this->name).($suffix ?: '');
		if (!Controller::ParamExists($param))
		{
			return false;
		}
		$this->SetValue(Controller::Param($param));
		return true;
	}
	
	public function IsColumn()
	{
		return true;
	}
	
	public function PreSave() {}
	public function PostSave() {}
	public function PreLoad() {}
	public function PostLoad() {}
	
}
