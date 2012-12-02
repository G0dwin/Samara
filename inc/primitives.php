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
	$string = Samara_RemovePrefix($string);
	return strtolower(preg_replace(array(
			'/([A-Z]s?)([A-Z]([a-z][^$]|[a-r|t-z]$))/',
			'/([a-z]|[0-9])([A-Z])/',
			'/([a-z]|[A-Z])([0-9])/',
	), '$1_$2', $string));
}

function Samara_RemovePrefix($string)
{
	$prefix = Samara_GetPrefix();
	$length = strlen($prefix);
	if ($length && substr($string, 0, $length) === $prefix)
	{
		return substr($string, $length);
	}
	return $string;
}

function Samara_ToCamelCase($string)
{
	return preg_replace_callback('/(^|_)([a-z0-9])/',
				create_function('$c', 'return strtoupper($c[2]);'),
				$string);
}

function Samara_CamelCaseToReadableCase($string)
{
	return preg_replace(array(
			'/([A-Z]s?)([A-Z]([a-z][^$]|[a-r|t-z]$))/',
			'/([a-z]|[0-9])([A-Z])/',
			'/([a-z]|[A-Z])([0-9])/',
	), '$1 $2', $string);
}
