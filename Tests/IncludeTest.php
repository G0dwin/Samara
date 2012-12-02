<?php

require_once 'Samara_TestCase.php';

class IncludeTest extends Samara_TestCase {
	
	public function __construct()
	{
		parent::__construct();
		$this->always_include = false;
	}
	
	public function testObjectsWorkNormallyWhenNoModulesExist()
	{
		Samara_Include('Test', 'Tests/artifacts');
		
		$obj = $this->new_Test();
		
		$this->assertEquals(7, $obj->Calculate(3, 4));
		$this->assertEquals('TEST A', $obj->ToString());
	}
	
	public function testObjectsCanOverrideMethods()
	{
		Samara_Around('Test', 'Tests/artifacts/Test2');
		
		Samara_Include('Test', 'Tests/artifacts');
	
		$obj = $this->new_Test();
	
		$this->assertEquals(12, $obj->Calculate(3, 4));
		$this->assertEquals('TEST A', $obj->ToString());
	}
	
	public function testCanSupportMultipleModulesForOneClass()
	{
		$class = $this->GetClass('Test');//$this->namespace.'\Test';
	
		Samara_Around('Test', 'Tests/artifacts/Test2');
		Samara_Around('Test', 'Tests/artifacts/Test3');
		
		Samara_Include('Test', 'Tests/artifacts');
	
		$obj = new $class();
	
		$this->assertEquals(12, $obj->Calculate(3, 4));
		$this->assertEquals('TEST B', $obj->ToString());
	}
	
	public function testCanSupportParentCalls()
	{
		$class = $this->namespace.'\Test';
	
		Samara_Around('Test', 'Tests/artifacts/Test2');
		Samara_Around('Test', 'Tests/artifacts/Test3');
		Samara_Around('Test', 'Tests/artifacts/Test4');
		
		Samara_Include('Test', 'Tests/artifacts');
	
		$obj = $this->new_Test();
	
		$this->assertEquals(-12, $obj->Calculate(3, 4));
		$this->assertEquals('TEST B', $obj->ToString());

	}
	
	public function testCanSupportIneritance()
	{
		Samara_Include('TestTest', 'Tests/artifacts');
	
		$obj = $this->new_TestTest();
		
		$this->assertEquals(70, $obj->Calculate(3, 4));
		$this->assertEquals('TESTTEST A', $obj->ToString());
	}
	
	public function testCanSupportInsertedIneritance()
	{
		Samara_Around('Test', 'Tests/artifacts/Test2');
	
		Samara_Include('TestTest', 'Tests/artifacts');
	
		$obj = $this->new_TestTest();
				
		$this->assertEquals(120, $obj->Calculate(3, 4));
		$this->assertEquals('TESTTEST A', $obj->ToString());
	}
	
}

