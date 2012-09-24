<?php

/*function Samara_GetClassName($class)
{
	$class = explode('\\', $class);
	return $class[count($class) - 1];
}

function Samara_GetFullClassName($class)
{
	global $samara_namespace;
	return strstr($class, '\\') == false ? "$samara_namespace\\$class" : $class;
}*/

function Samara_ToUnderscoreCase($string)
{
	if (is_object($string))
	{
		print_r(debug_backtrace());
	}
	return strtolower(preg_replace(array(
			'/([A-Z]s?)([A-Z]([a-z][^$]|[a-r|t-z]$))/',
			'/([a-z]|[0-9])([A-Z])/',
			'/([a-z]|[A-Z])([0-9])/',
	), '$1_$2', $string));
}

function Samara_ToCamelCase($string)
{
	return preg_replace_callback('/(^|_)([a-z0-9])/',
				create_function('$c', 'return strtoupper($c[2]);'),
				$string);
}
