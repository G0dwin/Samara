<?php

require_once 'Samara_TestCase.php';

class IntegerTest extends Samara_TestCase
{

	public function __construct()
	{
		parent::__construct();
		$this->file_location .= '/primitive_types';
	}
	
	/*
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructorWillTakeOneArgument()
	{
		$i = $this->NewTestObject('ItemCount');
		$this->assertEquals('ItemCount', $i->Name);
		$this->assertEquals('item_count', $i->NativeName);
		$this->assertEquals('`item_count`', $i->FormatName());
		$this->assertEquals('INT', $i->NativeType);
		$this->assertEquals(4, $i->Size);
		$this->assertNull($i->Value);
		$this->assertNull($i->NativeValue);
		$this->assertEquals('NULL', $i->FormatValue());
		$this->assertFalse($i->IsUnsigned(), 'Was unsigned');
		$this->assertFalse($i->IsNullable(), 'Was nullable');
		$this->assertFalse($i->DoesAutoincrement(), 'Did auto-increment');
		$this->assertFalse($i->IsPrimaryKey(), 'Was a primary key');
		$this->assertNotContains('UNSIGNED', $i->Properties, 'Contained unsigned property');
		$this->assertEquals(0, count($i->Properties));
		$fullName = $i->GetFullTypeName();
		$this->assertEquals('INT(4)', $fullName, "'$fullName' <> 'INT(4)'");
		
		$createString = '`item_count` INT(4) NOT NULL DEFAULT NULL';
		$actual = $i->CompileForCreate();
		$this->assertEquals($createString, $actual, "$actual <> $createString");
	}

	public function testConstructorWillTakeTwoArguments()
	{
		$i = $this->NewTestObject('Weight', 256);
		$this->assertEquals('Weight', $i->Name);
		$this->assertEquals('weight', $i->NativeName);
		$this->assertEquals('`weight`', $i->FormatName());
		$this->assertEquals('INT', $i->NativeType);
		$this->assertEquals(4, $i->Size);
		$this->assertEquals(256, $i->Value);
		$this->assertEquals(256, $i->NativeValue);
		$this->assertEquals('256', $i->FormatValue());
		$this->assertFalse($i->IsUnsigned(), 'Was unsigned');
		$this->assertFalse($i->IsNullable(), 'Was nullable');
		$this->assertFalse($i->DoesAutoincrement(), 'Did auto-increment');
		$this->assertFalse($i->IsPrimaryKey(), 'Was a primary key');
		$this->assertNotContains('UNSIGNED', $i->Properties, 'Contained unsigned property');
		$this->assertEquals(0, count($i->Properties));
		$fullName = $i->GetFullTypeName();
		$this->assertEquals('INT(4)', $fullName, "'$fullName' <> 'INT(4)'");
		
		$createString = '`weight` INT(4) NOT NULL DEFAULT NULL';
		$actual = $i->CompileForCreate();
		$this->assertEquals($createString, $actual, "$actual <> $createString");
	}

}

