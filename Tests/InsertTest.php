<?php

require_once 'Samara_TestCase.php';

/**
 * Insert test case.
 */
class InsertTest extends Samara_TestCase
{

	public function __construct()
	{
		parent::__construct();
		$this->file_location .= '/queries';
	}
	
	public function testSimpleInsert()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_Insert($Bicycle::Name('Henri'), $Bicycle::Price(129), $Bicycle::User(null));
		$sql = $q->Compile();
		$expected = 'INSERT INTO `bicycle` (`name`, `price`, `user_id`) VALUES (\'Henri\', 129, NULL);';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}
	
	/*public function testEasyInsert()
	{
		Samara_Include('Bicycle', 'Tests/artifacts/DomainObjects');
		$Bicycle = $this->GetClass('Bicycle');
		
		$q = $this->new_Insert($Bicycle::Name('Henri'), $Bicycle::Price(129), $Bicycle::User(null));
		$sql = $q->Compile();
		$expected = 'INSERT INTO `bicycle` (`name`, `price`, `user_id`) VALUES (\'Henri\', 129, NULL);';
		
		$this->assertEquals($sql, $expected, "$sql != $expected");
	}*/
	
}

