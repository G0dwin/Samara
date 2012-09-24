<?php

function Samara_Around($class, $file)
{
	global $samara_modules;
	if (!isset($samara_modules[$class]))
	{
		$samara_modules[$class] = array();
	}
	if (!isset($samara_modules[$class]['around']))
	{
		$samara_modules[$class]['around'] = array();
	}
	$samara_modules[$class]['around'][] = $file;
}

function Samara_GetAroundMods($class)
{
	global $samara_modules;
	$class = trim($class, '\\');
	if (!isset($samara_modules[$class]))
	{
		return null;
	}
	//throw new Exception($class);
	if (count($samara_modules) > 0)//strstr($class, 'Database'))
	{
	}
	if (!isset($samara_modules[$class]['around']))
	{
		return null;
	}
	//throw new Exception(count($samara_modules).'::'.$class.'::'.var_export($samara_modules, TRUE));
	return $samara_modules[$class]['around'];
}