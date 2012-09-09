<?php

require_once 'Samara_TestCase.php';

class IdentifierTest extends Samara_TestCase
{

	public function __construct()
	{
		parent::__construct();
		$this->file_location .= '/primitive_types';
	}
	
	/*
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructorWillTakeZeroArguments()
	{
		$i = $this->NewTestObject();
		$this->assertEquals('ID', $i->Name);
		$this->assertEquals('id', $i->NativeName);
		$this->assertEquals('`id`', $i->FormatName());
		$this->assertEquals('INT', $i->NativeType);
		$this->assertEquals(4, $i->Size);
		$this->assertNull($i->Value);
		$this->assertNull($i->NativeValue);
		$this->assertEquals('NULL', $i->FormatValue());
		$this->assertTrue($i->IsUnsigned(), 'Was not unsigned');
		$this->assertFalse($i->IsNullable(), 'Was nullable');
		$this->assertTrue($i->DoesAutoincrement(), 'Did not auto-increment');
		$this->assertTrue($i->IsPrimaryKey(), 'Was not a primary key');
		$this->assertContains('UNSIGNED', $i->Properties, 'Did not comtain undigned property');
		$this->assertEquals(1, count($i->Properties));
		$fullName = $i->GetFullTypeName();
		$this->assertEquals('INT(4) UNSIGNED', $fullName, "'$fullName' <> 'INT(4) UNSIGNED'");
		
		$createString = '`id` INT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT';
		$actual = $i->CompileForCreate();
		$this->assertEquals($createString, $actual, "$actual <> $createString");
	}

	public function testConstructorWillTakeOneArgument()
	{
		$i = $this->NewTestObject(256);
		$this->assertEquals('ID', $i->Name);
		$this->assertEquals('id', $i->NativeName);
		$this->assertEquals('`id`', $i->FormatName());
		$this->assertEquals('INT', $i->NativeType);
		$this->assertEquals(4, $i->Size);
		$this->assertEquals(256, $i->Value);
		$this->assertEquals(256, $i->NativeValue);
		$this->assertEquals('256', $i->FormatValue());
		$this->assertTrue($i->IsUnsigned(), 'Was not unsigned');
		$this->assertFalse($i->IsNullable(), 'Was nullable');
		$this->assertTrue($i->DoesAutoincrement(), 'Did not auto-increment');
		$this->assertTrue($i->IsPrimaryKey(), 'Was not a primary key');
		$this->assertContains('UNSIGNED', $i->Properties, 'Did not comtain undigned property');
		$this->assertEquals(1, count($i->Properties));
		$fullName = $i->GetFullTypeName();
		$this->assertEquals('INT(4) UNSIGNED', $fullName, "'$fullName' <> 'INT(4) UNSIGNED'");
		
		$createString = '`id` INT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT';
		$actual = $i->CompileForCreate();
		$this->assertEquals($createString, $actual, "$actual <> $createString");
	}
	
	public function testIsOutdated()
	{
		$i = $this->NewTestObject();
		
		$tests = array(
					array(
						array(
							'Field' => 'id',
							'Type' => 'int(4) unsigned',
							'Null' => 'NO',
							'Key' => 'PRI',
							'Default' => null,
							'Extra' => 'auto_increment'
						), FALSE),
					array(
						array(
							'Field' => 'id',
							'Type' => 'int(4)',
							'Null' => 'NO',
							'Key' => 'PRI',
							'Default' => null,
							'Extra' => 'auto_increment'
						), TRUE),
					array(
						array(
							'Field' => 'id',
							'Type' => 'int(4) unsigned',
							'Null' => 'YES',
							'Key' => 'PRI',
							'Default' => null,
							'Extra' => 'auto_increment'
						), TRUE),
					array(
						array(
							'Field' => 'id',
							'Type' => 'int(4) unsigned',
							'Null' => 'NO',
							'Key' => '',
							'Default' => null,
							'Extra' => 'auto_increment'
						), TRUE),
					array(
						array(
							'Field' => 'id',
							'Type' => 'int(4) unsigned',
							'Null' => 'NO',
							'Key' => 'PRI',
							'Default' => null,
							'Extra' => ''
						), TRUE),
					array(
						array(
							'Field' => 'id',
							'Type' => 'int(8) unsigned',
							'Null' => 'NO',
							'Key' => 'PRI',
							'Default' => null,
							'Extra' => 'auto_increment'
						), TRUE),
					array(
						array(
							'Field' => 'id',
							'Type' => 'int(4) unsigned',
							'Null' => 'NO',
							'Key' => 'PRI',
							'Default' => 0,
							'Extra' => 'auto_increment'
						), TRUE),
		);
		foreach ($tests AS $test)
		{
			$assert = $test[1] ? 'assertTrue' : 'assertFalse';
			$this->$assert($i->IsOutdated($test[0]), 'IsOutdated() '.$assert.' TestFailed: '.var_export($test[0], TRUE).' ('.implode(' ', $i->GetProperties()).')');
		}
	}

}

