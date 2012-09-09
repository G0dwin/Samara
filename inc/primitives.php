<?php

function Samara_GetClassName($class)
{
	$class = explode('\\', $class);
	return $class[count($class) - 1];
}

function Samara_GetFullClassName($class)
{
	global $samara_namespace;
	return "$samara_namespace\\$class";
}

function Samara_ToUnderscoreCase($string)
{
	return strtolower(preg_replace(array(
			'/([A-Z]s?)([A-Z]([a-z][^$]|[a-r|t-z]$))/',
			'/([a-z]|[0-9])([A-Z])/',
			'/([a-z]|[A-Z])([0-9])/',
	), '$1_$2', $string));
}