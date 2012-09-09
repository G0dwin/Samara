<?php

require_once 'Samara_TestCase.php';

class DatabaseTest extends Samara_TestCase
{

	public function __construct()
	{
		parent::__construct();
	}

	public static function __callStatic($name, $args)
	{
		$bt = debug_backtrace();
		$obj = $bt[2]['object'];
		$class = $obj->GetClass();
		return $class::__callStatic($name, $args);
	}
	
	public function testIsSingleton()
	{
		$Database = $this->GetClass();
		$db1 = $Database::DB();
		$db2 = $Database::DB();
		$this->assertTrue($db1 === $db2);
	}

	public function testFormatNull()
	{
		$this->assertEquals('NULL', $this::FormatNull());
	}
	
	public function testFormatInteger()
	{
		$tests = array(
						0 => 0,
						1 => 1,
						-4 => -4,
						'9999999999' => 9999999999,
						'NULL' => null
					);
		foreach ($tests as $output => $input)
		{
			$this->assertEquals($output, $this::FormatInteger($input));
		}
	}
	
	public function testFormatString()
	{
			$tests = array(
						array('\'0\'', 0),
						array('\'1\'', 1),
						array('\'TEST\'', 'TEST'),
						array('\'\'', ''),
						array('\'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\'', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
						array('NULL', null)
					);
		foreach ($tests as $test)
		{
			$this->assertEquals($test[0], $this::FormatString($test[1]));
		}
	}

	public function testFormatAll()
	{
		$this->assertEquals('*', $this::FormatAll());
	}
	
	public function testFormatTable()
	{
		$tests = array(
						'`customers`' => 'customers',
						'`user_profile`' => 'user_profile',
					);
		foreach ($tests as $output => $input)
		{
			$this->assertEquals($output, $this::FormatTable($input));
		}
	}
	
	public function FormatColumn()
	{
			$tests = array(
						'`id`' => 'id',
						'`customer_id`' => 'customer_id',
					);
		foreach ($tests as $output => $input)
		{
			$this->assertEquals($output, $this::FormatColumn($input));
		}
	}
}
