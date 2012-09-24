<?php

define('SAMARA_TEST',	0x1);	// For unit testing: least secure, uses eval()
define('SAMARA_DEV',	0x2);	// For development: always recompiles cache
define('SAMARA_PROD',	0x3);	// For production: comiples when out of date

define('SAMARA_ROOT', __DIR__.'/');
define('SAMARA_CACHE_DIR', SAMARA_ROOT.'class_cache/');
define('SAMARA_EXTENSIONS_DIR', SAMARA_ROOT.'extensions/');
define('SAMARA_BUILD', SAMARA_DEV);
define('SAMARA_PREFIX', '');

require_once 'settings.php';
require_once 'inc/include.php';
require_once 'inc/primitives.php';

// initalize all modules
Samara_LoadExtensions();

//include our controller class
Samara_Include('Controller', 'inc');
// and make it render the display
Controller::Render();
