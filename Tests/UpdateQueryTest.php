<?php

require_once ('Tests/Samara_TestCase.php');

class UpdateQueryTest extends Samara_TestCase
{
	public function __construct()
	{
		parent::__construct();
		$this->file_location .= '/queries';
	}
	
	public function testSimpleUpdate()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0));
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	public function testUpdateWhere()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0))->Where($Bicycle::ID()->Equals('10'));
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0 WHERE `id` = 10;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	public function testUpdateWhereOr()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0))->Where($Bicycle::ID()->Equals('10'))->Or($Bicycle::Name()->IsNull());
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0 WHERE `id` = 10 OR `name` IS NULL;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	public function testUpdateLimit()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0))->Limit(2);
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0 LIMIT 2;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	public function testUpdateWhereLimit()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0))->Where($Bicycle::Price()->IsNull())->Limit(2);
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0 WHERE `price` IS NULL LIMIT 2;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	public function testUpdateOrderBy()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0))->Where($Bicycle::Price()->IsNull())->OrderBy($Bicycle::Name())->Limit(2);
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0 WHERE `price` IS NULL ORDER BY `name` LIMIT 2;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	public function testUpdateOrderByAsc()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0))->Where($Bicycle::Price()->IsNull())->OrderBy($Bicycle::Name()->Asc())->Limit(2);
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0 WHERE `price` IS NULL ORDER BY `name` ASC LIMIT 2;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	public function testUpdateOrderByDesc()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_UpdateQuery($Bicycle::Price(0))->Where($Bicycle::Price()->IsNull())->OrderBy($Bicycle::Name()->Desc())->Limit(2);
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0 WHERE `price` IS NULL ORDER BY `name` DESC LIMIT 2;';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}

	public function testUpdateMultipleColumns()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
	
		$q = $this->new_UpdateQuery($Bicycle::Price(0), $Bicycle::Name(null))->Where($Bicycle::Price()->IsNull())->OrderBy($Bicycle::Name()->Asc())->Limit(2);
		$sql = $q->Compile();
		$expected = 'UPDATE `bicycle` SET `price` = 0, `name` = NULL WHERE `price` IS NULL ORDER BY `name` ASC LIMIT 2;';
	
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
}

?>