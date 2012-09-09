<?php

require_once 'inc/modules.php';
require_once 'inc/primitives.php';

function Samara_GetFile($filename)
{
	$filepath = SAMARA_ROOT.$filename.'.sphp';
	if (!file_exists($filepath))
	{
		throw new \Exception("File \'$filepath\' does not exist");
	}
	$file = preg_replace('/(^\s*<\?\s*php\s*)|(\?>\s*$)/', '', file_get_contents($filepath));
	return $file;
}

function Samara_Reclass($contents, $old_class, $new_class, $parent, $is_first)
{
	$result = $contents;
	$result = preg_replace('/(abstract\s*)?(class\s*)'.$old_class.'(\s*extends\s*[^\s]*)?(\s*implements\s*[^\{]*)?(\s*\{)/', "\n\n".($is_first ? '$1' : 'abstract ').'$2'.$new_class.($parent ? ' extends '.$parent : '$3').'$4$5', $result);
	//$result = preg_replace('/^(\s*)(Samara_Include\(.*?\);)+(.*$)/', '$1$3$2', $result);
		
	return $result;
}

function Samara_IncludeContents($contents, $namespace, $class, $dir)
{
	global $samara_include_method;
	if ($samara_include_method == SAMARA_TEST)
	{
		eval($contents);
	}
	else
	{
		$parts = explode('/', $dir);
		$curr_dir = SAMARA_ROOT.'/class_cache';
		foreach ($parts as $part)
		{
			if(!is_dir($curr_dir .= "/$part"))
			{
				mkdir($curr_dir);
			}
		}
		
		$filename = SAMARA_ROOT.'/class_cache/'.$dir.'/'.$class.'.php';
		file_put_contents($filename, '<?php '.$contents);
		include $filename;
	}
}

function Samara_ClassExists($class, $dir)
{
	global $samara_namespace;
	
	$full_class = $samara_namespace.'\\'.$class;
	if (class_exists($full_class))
	{
		return TRUE;
	}
	return file_exists(SAMARA_ROOT.$dir.'/'.$class.'.sphp');
}

function Samara_Include($class, $dir)
{
	global $samara_namespace;
	
	$full_class = $samara_namespace.'\\'.$class;
	if (class_exists($full_class))
	{
		return;
	}
	
	$namespace = $samara_namespace ? 'namespace '.$samara_namespace.'; ' : '';
	
	$overrides = Samara_GetAroundMods($class);
	
	$filename = $dir.'/'.$class;
	
	if (!$overrides)
	{
		$file = Samara_GetFile($filename);
		Samara_IncludeContents($namespace.$file, $samara_namespace, $class, $dir);
		return;
	}

	$contents = array();
	$contents[] = Samara_GetFile($filename);
	foreach ($overrides as $file)
	{
		$contents[] = Samara_GetFile($file);
	}
	$count = count($contents) - 1;
	$parent = null;
	foreach ($contents as $content)
	{
		$new_class = str_repeat('_', $count).$class;
		$file = Samara_Reclass($content, $class, $new_class, $parent, !$count);
		Samara_IncludeContents($namespace.$file, $samara_namespace, $new_class, $dir);
		$parent = $new_class;
		$count--;
	}
}
