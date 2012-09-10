<?php

define('SAMARA_TEST',	0x1);	// For unit testing: least secure, uses eval()
define('SAMARA_DEV',	0x2);	// For development: always recompiles cache
define('SAMARA_PROD',	0x3);	// For production: comiples when out of date

define('SAMARA_ROOT', "C:\\Users\\Godwin\\My Sites\\Samara\\");

$samara_modules = array();
$samara_namespace = null;
$samara_include_method = SAMARA_DEV;
$samara_theme = 'Basic';
$samara_db_info = array
					(
						'host' => 'localhost',
						'username' => 'samara',
						'passwd' => 'samara',
						'dbname' => 'samara',
						'port' => 3306,
						'socket' => NULL
					);
