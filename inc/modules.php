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
	if (!isset($samara_modules[$class]))
	{
		return null;
	}
	if (!isset($samara_modules[$class]['around']))
	{
		return null;
	}
	return $samara_modules[$class]['around'];
}