<?php

require_once ('Tests/Samara_TestCase.php');

class UpdateManagerTest extends Samara_TestCase
{
	public function setUp()
	{
		/*global $samara_modules;
		$samara_modules = array();
		Samara_Around('Database', 'Tests/artifacts/MockDatabase');
		Samara_Around('UpdateManager', 'Tests/artifacts/MockUpdateManager');
		$this->reset_modules = FALSE;
		parent::setUp();*/
		//throw new Exception(class_exists('Database') ? 'TRUE' : 'FALSE');
		Samara_Include('Database', 'inc');
	}
	
	public function testGetTableList()
	{
		//global $samara_modules;
		//print_r($samara_modules);
		
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(array('bicycle'), array('user'))));
		
		$result = $UpdateManager::GetTableList();
		
		$queries_called = $Database::GetQueriesCalled();
		$this->assertEquals(1, count($queries_called));
		$this->assertEquals('SHOW TABLES;', $queries_called[0]);
		$this->assertEquals(2, count($result));
		$this->assertContains('bicycle', $result);
		$this->assertContains('user', $result);
	}
	
	public function testGetDomainDir()
	{
		$UpdateManager = $this->GetClass();
		$this->assertEquals('Tests/artifacts/DomainObjects', $UpdateManager::GetDomainDir());
	}

	public function testGetDomainObjectList()
	{
		$UpdateManager = $this->GetClass();
		$classes = $UpdateManager::GetDomainObjectList();
		
		$this->assertEquals(2, count($classes));
		$this->assertContains('Bicycle', $classes);
		$this->assertContains('User', $classes);
	}
	
	public function testCreateUpdateScriptShouldReturnFalseWhenNoUpdateIsRequired()
	{
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(
											array('bicycle'), array('user')
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI',
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'price',
												'Type' => 'int(4)',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'user_id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI', 
												'Default' => null,
												'Extra' => 'auto_increment'
											),
										array(
											'Field' => 'name',
											'Type' => 'varchar',
											'Null' => 'NO',
											'Key' => '',
											'Default' => null,
											'Extra' => ''
										),
									),
								));
		
		$result = $UpdateManager::CreateUpdateScript();
		
		$this->assertFalse($result, "$result <> FALSE");
		
	}
	
	public function testCreateUpdateScriptShouldReturnFalseWhenExtraTablesExist()
	{
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(
											array('bicycle'), array('user'), array('test')
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI',
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'price',
												'Type' => 'int(4)',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'user_id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI', 
												'Default' => null,
												'Extra' => 'auto_increment'
											),
										array(
											'Field' => 'name',
											'Type' => 'varchar',
											'Null' => 'NO',
											'Key' => '',
											'Default' => null,
											'Extra' => ''
										),
									),
								));
		
		$result = $UpdateManager::CreateUpdateScript();
		
		$this->assertFalse($result, "$result <> FALSE");
		
	}
	
	public function testCreateUpdateScriptShouldCreateTableIfNtExist()
	{
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(
											array('bicycle'),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI',
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'price',
												'Type' => 'int(4)',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'user_id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI', 
												'Default' => null,
												'Extra' => 'auto_increment'
											),
										),
									));
		
		$result = $UpdateManager::CreateUpdateScript();
		$expected = 'CREATE TABLE `user` (`id` INT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR NOT NULL DEFAULT NULL);';
		
		$this->assertEquals($expected, $result, "$result <> $expected");
		
	}
	
	public function testCreateUpdateScriptShouldAddMissingColumn()
	{
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(
											array('bicycle'), array('user')
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI',
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'price',
												'Type' => 'int(4)',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI', 
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
									));
		
		$result = $UpdateManager::CreateUpdateScript();
		$expected = 'ALTER TABLE `bicycle` ADD COLUMN `user_id` INT(4) UNSIGNED NOT NULL DEFAULT NULL;';
		
		$this->assertEquals($expected, $result, "'$result' <> '$expected'");
	}
	
	public function testCreateUpdateScriptShouldDropUnusedColumn()
	{
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(
											array('bicycle'), array('user')
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI',
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'price',
												'Type' => 'int(4)',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'user_id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI', 
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
													'Field' => 'bicycle_id',
													'Type' => 'int(4) unsigned',
													'Null' => 'NO',
													'Key' => '',
													'Default' => null,
													'Extra' => ''
											),
										),
									));
		
		$result = $UpdateManager::CreateUpdateScript();
		$expected = 'ALTER TABLE `user` DROP COLUMN `bicycle_id`;';
		
		$this->assertEquals($expected, $result, "'$result' <> '$expected'");
	}
	
	public function testCreateUpdateScriptShouldModifyColumn()
	{
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(
											array('bicycle'), array('user')
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI',
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'price',
												'Type' => 'int(4)',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'YES',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'user_id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI', 
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
									));
		
		$result = $UpdateManager::CreateUpdateScript();
		$expected = 'ALTER TABLE `bicycle` MODIFY COLUMN `name` VARCHAR NOT NULL DEFAULT NULL;';
		
		$this->assertEquals($expected, $result, "'$result' <> '$expected'");
	}
	
	public function testCreateUpdateScriptShouldPerformMultipleOperations()
	{
		$UpdateManager = $this->GetClass();
		$Database = $this->GetClass('Database');
		
		$Database::SetExpectedQueryResults(array(array(
											array('bicycle')
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI',
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'price',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'alias',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
											array(
												'Field' => 'user_id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
										array(
											array(
												'Field' => 'id',
												'Type' => 'int(4) unsigned',
												'Null' => 'NO',
												'Key' => 'PRI', 
												'Default' => null,
												'Extra' => 'auto_increment'
											),
											array(
												'Field' => 'name',
												'Type' => 'varchar',
												'Null' => 'NO',
												'Key' => '',
												'Default' => null,
												'Extra' => ''
											),
										),
									));
		
		$result = $UpdateManager::CreateUpdateScript();
		$expected = "ALTER TABLE `bicycle` MODIFY COLUMN `price` INT(4) NOT NULL DEFAULT NULL, ADD COLUMN `name` VARCHAR NOT NULL DEFAULT NULL, DROP COLUMN `alias`;\nCREATE TABLE `user` (`id` INT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR NOT NULL DEFAULT NULL);";
		
		$this->assertEquals($expected, $result, "'$result' <> '$expected'");
	}
	
}

