<?php

require_once 'Samara_TestCase.php';

class SelectQueryTest extends Samara_TestCase
{

	public function __construct()
	{
		parent::__construct();
		$this->file_location .= '/queries';
	}

	public function testSimpleIntSelect()
	{
		$q = $this->NewTestObject(5);
		$sql = $q->Compile();
		$this->assertEquals('SELECT 5;', $sql);
	}

	public function testSimpleStringSelect()
	{
		$q = $this->NewTestObject('test');
		$sql = $q->Compile();
		$this->assertEquals('SELECT \'test\';', $sql);
	}

	public function testSimpleNullSelect()
	{
		$q = $this->NewTestObject(null);
		$sql = $q->Compile();
		$this->assertEquals('SELECT NULL;', $sql);
	}

	public function testSelectMultipleValues()
	{
		$q = $this->NewTestObject(5, 'test');
		$sql = $q->Compile();
		$this->assertEquals('SELECT 5, \'test\';', $sql);
	}

	public function testSelectAllFromTable()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All());
		$sql = $q->Compile();
		$expected = 'SELECT * FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testSelectOneValueFromTable()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Price());
		$sql = $q->Compile();
		$expected = 'SELECT `price` FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testSelectTwoValuesFromTable()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testSelectTwoValuesFromTwoTables()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price(), $User::ID());
		$sql = $q->Compile();
		$expected = 'SELECT `bicycle`.`name`, `bicycle`.`price`, `user`.`id` FROM `bicycle`, `user`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testValuesWithAliasedColumn()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::Price('Cost'));
		$sql = $q->Compile();
		$expected = 'SELECT `price` AS `cost` FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testValuesWithAliasedTable()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject($TableAlias::Bike($Bicycle::Name()));
		$sql = $q->Compile();
		$expected = 'SELECT `name` FROM `bicycle` AS `bike`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testValuesWithTwoAliasedTables()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject($TableAlias::Bicycle1($Bicycle::Name('Name1')), $TableAlias::Bicycle2($Bicycle::Name('Name2')));

		$sql = $q->Compile();
		$expected = 'SELECT `bicycle_1`.`name` AS `name_1`, `bicycle_2`.`name` AS `name_2` FROM `bicycle` AS `bicycle_1`, `bicycle` AS `bicycle_2`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testMax()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Price()->Max());
		$sql = $q->Compile();
		$expected = 'SELECT MAX(`price`) FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testMaxWithAutoAlias()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Price()->Max(TRUE));
		$sql = $q->Compile();
		$expected = 'SELECT MAX(`price`) AS `max_price` FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testMaxWithAlias()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Price()->Max('UpperCost'));
		$sql = $q->Compile();
		$expected = 'SELECT MAX(`price`) AS `upper_cost` FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testAddition()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Price()->Plus(5));
		$sql = $q->Compile();
		$expected = 'SELECT `price` + 5 FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testAddIntToString()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Name()->Plus(5));
		$sql = $q->Compile();
		$expected = "SELECT `name` + '5' FROM `bicycle`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testAdditionAndMultiplicationAddBrackets()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Price()->Plus(5)->Times(10));
		$sql = $q->Compile();
		$expected = 'SELECT (`price` + 5) * 10 FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testAdditionAndMultiplicationAddBracketsSecond()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Price()->Plus($Bicycle::Price()->Times(10)));
		$sql = $q->Compile();
		$expected = 'SELECT `price` + (`price` * 10) FROM `bicycle`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testCountIsInteger()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Name()->Count()->Plus(5));
		$sql = $q->Compile();
		$expected = "SELECT COUNT(`name`) + 5 FROM `bicycle`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testMaxIsNotAnInteger()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Name()->Max()->Plus(5));
		$sql = $q->Compile();
		$expected = "SELECT MAX(`name`) + '5' FROM `bicycle`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testNegate()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');

		$q = $this->NewTestObject($Bicycle::Price()->Negate());
		$sql = $q->Compile();
		$expected = "SELECT -`price` FROM `bicycle`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testCoalesce()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Price()->Coalesce($Bicycle::Name(), $Bicycle::ID(), 5));
		$sql = $q->Compile();
		$expected = "SELECT COALESCE(`price`, `name`, `id`, 5) FROM `bicycle`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	/*
	 * 	WHERE Statements...
	 */

	public function testWhereTrue()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where(1);
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE 1;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testWherePriceIsZero()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where($Bicycle::Price()->Equals(0));
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` = 0;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testWhereIsNull()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where($Bicycle::Price()->IsNull());
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` IS NULL;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testWhereBetween()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where($Bicycle::Price()->IsBetween(5, 10));
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` BETWEEN 5 AND 10;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testAndAsList()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where($Bicycle::Price()->Equals(5), $Bicycle::ID()->Equals(10));
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` = 5 AND `id` = 10;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testAndAsFunction()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where($Bicycle::Price()->Equals(5)->And($Bicycle::ID()->Equals(10)));
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` = 5 AND `id` = 10;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testOr()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where($Bicycle::Price()->Equals(5)->Or($Bicycle::Price()->Equals(10)));
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` = 5 OR `price` = 10;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testOrAfterWhere()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where($Bicycle::Price()->Equals(5))->Or($Bicycle::Price()->Equals(10));
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` = 5 OR `price` = 10;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testAndWithOr()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where(
								$Bicycle::Price()->Equals(5)->
								And(
									$Bicycle::ID()->Equals(10)->
									Or(
										$Bicycle::Price()->IsNull()
									)
								)
							);
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` = 5 AND (`id` = 10 OR `price` IS NULL);";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testOrWithAnd()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where(
								$Bicycle::Price()->Equals(5),
								$Bicycle::ID()->Equals(10)
							)->Or(
								$Bicycle::Price()->IsNull()
							);
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE (`price` = 5 AND `id` = 10) OR `price` IS NULL;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testMultipleAnds()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where(
								$Bicycle::Price()->Equals(5),
								$Bicycle::ID()->Equals(10),
								$Bicycle::Name()->IsNotNull()
							);
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE `price` = 5 AND `id` = 10 AND `name` IS NOT NULL;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testOrWithMultipleAnds()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::All())->Where(
								$Bicycle::Price()->Equals(5),
								$Bicycle::ID()->Equals(10),
								$Bicycle::Name()->IsNotNull()
							)->Or(
								$Bicycle::Price()->IsNull()->And(
									$Bicycle::ID()->GreaterThan(100),
									$Bicycle::Name()->IsNotNull()
								)
							);
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` WHERE (`price` = 5 AND `id` = 10 AND `name` IS NOT NULL) OR (`price` IS NULL AND `id` > 100 AND `name` IS NOT NULL);";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testWhereWithSimpleJoin()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::All(), $User::All())->Where(
								$Bicycle::ID()->Equals($User::ID())
							)->Or(
								$Bicycle::Price()->IsNull()
							);
		$sql = $q->Compile();
		$expected = "SELECT `bicycle`.*, `user`.* FROM `bicycle`, `user` WHERE `bicycle`.`id` = `user`.`id` OR `bicycle`.`price` IS NULL;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	/*
	 * 	JOINs
	 */
	
	public function testSimpleJoin()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::All(), $User::All())->Join($User::On($User::Equals($Bicycle::User())));
		$sql = $q->Compile();
		$expected = "SELECT `bicycle`.*, `user`.* FROM `bicycle` JOIN `user` ON `user`.`id` = `bicycle`.`user_id`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testCrossJoin()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::All(), $User::All())->CrossJoin($User);
		$sql = $q->Compile();
		$expected = "SELECT `bicycle`.*, `user`.* FROM `bicycle` JOIN `user`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testSimplfiedJoin()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::All(), $User::All())->Join($User);
		$sql = $q->Compile();
		$expected = "SELECT `bicycle`.*, `user`.* FROM `bicycle` JOIN `user` ON `user`.`id` = `bicycle`.`user_id`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testSimplfiedSelect()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle);
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testSimplfiedSelectWithAlias()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject($TableAlias::Bike($Bicycle));
		$sql = $q->Compile();
		$expected = "SELECT * FROM `bicycle` AS `bike`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testLeftJoin()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::All(), $User::All())->LeftJoin($User);
		$sql = $q->Compile();
		$expected = "SELECT `bicycle`.*, `user`.* FROM `bicycle` LEFT JOIN `user` ON `user`.`id` = `bicycle`.`user_id`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testJoinOnSameTable()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject($TableAlias::Bike1($Bicycle), $TableAlias::Bike2($Bicycle))->Join($TableAlias::Bike2($Bicycle)->On($TableAlias::Bike1($Bicycle::Name())->Equals($TableAlias::Bike2($Bicycle::Name()))));
		$sql = $q->Compile();
		$expected = "SELECT `bike_1`.*, `bike_2`.* FROM `bicycle` AS `bike_1` JOIN `bicycle` AS `bike_2` ON `bike_1`.`name` = `bike_2`.`name`;";
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testMultipleJoins()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject($TableAlias::Bike1($Bicycle), $TableAlias::Bike2($Bicycle))->Join($TableAlias::Bike2($Bicycle)->On($TableAlias::Bike1($Bicycle::Name())->Equals($TableAlias::Bike2($Bicycle::Name()))))->LeftJoin($User);
		$sql = $q->Compile();
		$expected = "SELECT `bike_1`.*, `bike_2`.* FROM `bicycle` AS `bike_1` JOIN `bicycle` AS `bike_2` ON `bike_1`.`name` = `bike_2`.`name` LEFT JOIN `user` ON `user`.`id` = `bike_1`.`user_id`;";
		$this->assertEquals($expected, $sql, "\n$sql != \n$expected");
	}
	
	public function testJoinWithMultipleConditions()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject(
					$TableAlias::Bike1($Bicycle),
					$TableAlias::Bike2($Bicycle)
				)->Join(
						$TableAlias::Bike2($Bicycle)->
						On(
							$TableAlias::Bike1($Bicycle::Name())->Equals(
									$TableAlias::Bike2($Bicycle::Name())))
								->Or($TableAlias::Bike1($Bicycle::Name())->IsNull()));
		$sql = $q->Compile();
		$expected = "SELECT `bike_1`.*, `bike_2`.* FROM `bicycle` AS `bike_1` JOIN `bicycle` AS `bike_2` ON `bike_1`.`name` = `bike_2`.`name` OR `bike_1`.`name` IS NULL;";
		$this->assertEquals($expected, $sql, "\n$sql != \n$expected");
	}
	
	/*
	 * 	LIMIT
	 */
	
	public function testSimpleLimit()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject(
					$TableAlias::Bike1($Bicycle),
					$TableAlias::Bike2($Bicycle)
				)->Join(
						$TableAlias::Bike2($Bicycle)->
						On(
							$TableAlias::Bike1($Bicycle::Name())->Equals(
									$TableAlias::Bike2($Bicycle::Name())))
								->Or($TableAlias::Bike1($Bicycle::Name())->IsNull()))->Limit(5);
		$sql = $q->Compile();
		$expected = "SELECT `bike_1`.*, `bike_2`.* FROM `bicycle` AS `bike_1` JOIN `bicycle` AS `bike_2` ON `bike_1`.`name` = `bike_2`.`name` OR `bike_1`.`name` IS NULL LIMIT 5;";
		$this->assertEquals($expected, $sql, "\n$sql != \n$expected");
	}

	public function testLimitRange()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		Samara_Include('TableAlias', 'inc/queries');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject(
				$TableAlias::Bike1($Bicycle),
				$TableAlias::Bike2($Bicycle)
		)->Join(
				$TableAlias::Bike2($Bicycle)->
				On(
						$TableAlias::Bike1($Bicycle::Name())->Equals(
								$TableAlias::Bike2($Bicycle::Name())))
				->Or($TableAlias::Bike1($Bicycle::Name())->IsNull()))->Limit(5, 10);
		$sql = $q->Compile();
		$expected = "SELECT `bike_1`.*, `bike_2`.* FROM `bicycle` AS `bike_1` JOIN `bicycle` AS `bike_2` ON `bike_1`.`name` = `bike_2`.`name` OR `bike_1`.`name` IS NULL LIMIT 5, 10;";
		$this->assertEquals($expected, $sql, "\n$sql != \n$expected");
	}
	
	/*
	 * Sub Select Queries
	 */
	
	public function testSimpleSubQuery()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject($Bicycle::Name(), $this->NewTestObject($User::Name()->Count())->Where($User::Name()->Equals($Bicycle::Name()))->As('UserMatches'));
		$sql = $q->Compile();
		$expected = "SELECT `name`, (SELECT COUNT(`user`.`name`) FROM `user` WHERE `user`.`name` = `bicycle`.`name`) AS `user_matches` FROM `bicycle`;";
		$this->assertEquals($expected, $sql, "\n$sql != \n$expected");
	}
	
	public function testSubQueryInWhere()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$TableAlias = $this->GetClass('TableAlias');
		$q = $this->NewTestObject($Bicycle::Name())->Where($this->NewTestObject($User::Name()->Count())->Where($User::Name()->Equals($Bicycle::Name()))->GreaterThan(0));
		$sql = $q->Compile();
		$expected = "SELECT `name` FROM `bicycle` WHERE (SELECT COUNT(`user`.`name`) FROM `user` WHERE `user`.`name` = `bicycle`.`name`) > 0;";
		$this->assertEquals($expected, $sql, "\n$sql != \n$expected");
	}
	
	/*
	 * 	ORDER BY
	 */
	
	public function testSimpleOrderBy()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->OrderBy($Bicycle::Price());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` ORDER BY `price`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testOrderByAscending()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->OrderBy($Bicycle::Price()->Asc());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` ORDER BY `price` ASC;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testOrderByDescending()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->OrderBy($Bicycle::Price()->Desc());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` ORDER BY `price` DESC;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testOrderByMultipleColumns()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->OrderBy($Bicycle::Price()->Desc(), $Bicycle::ID());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` ORDER BY `price` DESC, `id`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testOrderBySubQuery()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->OrderBy($this->NewTestObject($User::Name()->Count())->Where($User::Name()->Equals($Bicycle::Name())));
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` ORDER BY (SELECT COUNT(`user`.`name`) FROM `user` WHERE `user`.`name` = `bicycle`.`name`);';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testOrderBySubQueryDescending()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->OrderBy($this->NewTestObject($User::Name()->Count())->Where($User::Name()->Equals($Bicycle::Name()))->Desc());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` ORDER BY (SELECT COUNT(`user`.`name`) FROM `user` WHERE `user`.`name` = `bicycle`.`name`) DESC;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	/*
	 * 	GROUP BY
	 */
	
	public function testSimpleGroupBy()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->GroupBy($Bicycle::Price());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` GROUP BY `price`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testGroupByAscending()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->GroupBy($Bicycle::Price()->Asc());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` GROUP BY `price` ASC;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testGroupByDescending()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->GroupBy($Bicycle::Price()->Desc());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` GROUP BY `price` DESC;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testGroupByMultipleColumns()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->GroupBy($Bicycle::Price()->Desc(), $Bicycle::ID());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` GROUP BY `price` DESC, `id`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testGroupBySubQuery()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->GroupBy($this->NewTestObject($User::Name()->Count())->Where($User::Name()->Equals($Bicycle::Name())));
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` GROUP BY (SELECT COUNT(`user`.`name`) FROM `user` WHERE `user`.`name` = `bicycle`.`name`);';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testGroupBySubQueryDescending()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		Samara_Include('User', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$User = $this->GetClass('User');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->GroupBy($this->NewTestObject($User::Name()->Count())->Where($User::Name()->Equals($Bicycle::Name()))->Desc());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` GROUP BY (SELECT COUNT(`user`.`name`) FROM `user` WHERE `user`.`name` = `bicycle`.`name`) DESC;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}

	public function testGroupByWithOrderBy()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->GroupBy($Bicycle::Price())->OrderBy($Bicycle::ID());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` GROUP BY `price` ORDER BY `id`;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	/*
	 * 	HAVING
	 */
	
	public function testSimpleHaving()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->Having($Bicycle::Price()->Max());
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` HAVING MAX(`price`);';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
	public function testSimpleHavingCompare()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		$q = $this->NewTestObject($Bicycle::Name(), $Bicycle::Price())->Having($Bicycle::Price()->Max()->GreaterThan(1));
		$sql = $q->Compile();
		$expected = 'SELECT `name`, `price` FROM `bicycle` HAVING MAX(`price`) > 1;';
		$this->assertEquals($expected, $sql, "$sql != $expected");
	}
	
}

