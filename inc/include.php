<?php

require_once 'inc/modules.php';
require_once 'inc/primitives.php';

function Samara_GetFile($filename)
{
	$filepath = SAMARA_ROOT.$filename.'.sphp';
	if (!file_exists($filepath))
	{
		$extensions = glob(SAMARA_ROOT.str_replace('inc/', 'extensions/*/', $filename).'.sphp');
		if (!$extensions || empty($extensions))
		{
			throw new \Exception("File \'$filepath\' does not exist");
		}
		$filepath = $extensions[0];
	}
	$file = preg_replace('/(^\s*<\?\s*php\s*)|(\?>\s*$)/', '', file_get_contents($filepath));
	return $file;
}

function Samara_Reclass($contents, $old_class, $new_class, $parent, $is_first)
{
	$result = $contents;
	$result = preg_replace('/(abstract\s*)?(class\s*)'.$old_class.'(\s*extends\s*[^\s]*)?(\s*implements\s*[^\{]*)?(\s*\{)/', "\r\n\r\n".($is_first ? '$1' : 'abstract ').'$2'.SAMARA_PREFIX.$new_class.($parent ? ' extends '.SAMARA_PREFIX.$parent : '$3').'$4$5', $result);
	//$result = preg_replace('/(?<!~)~(?!~)/', SAMARA_PREFIX.'_', $result);
	//$result = str_replace('~~', '~', $result);
	
	if (SAMARA_PREFIX)
	{
		static $classes = array();
		$classes[$old_class] = SAMARA_PREFIX.$old_class;
		
		foreach ($classes as $old => $new)
		{
			$result = preg_replace('/\\b(?<!~)'.$old.'\\b/', $new, $result);
			$result = preg_replace('/\\b~'.$old.'\\b/', $old, $result);
		}
	}
		
	return $result;
}

function Samara_IncludeContents($contents, $class, $dir)
{
	global $samara_include_method;
	$fullclass = SAMARA_PREFIX.$class;//$namespace.'\\'.$class;
	//$cache_dir = SAMARA_CACHE_DIR;//($samara_include_method == SAMARA_TEST ? SAMARA_ROOT.'Tests/class_cache/'.$namespace.'/' : SAMARA_ROOT.'class_cache/');
	$parts = explode('/', $dir);
	$curr_dir = SAMARA_CACHE_DIR;
	if (!is_dir($curr_dir))
	{
		mkdir($curr_dir);
	}
	foreach ($parts as $part)
	{
		if (!is_dir($curr_dir .= "/$part"))
		{
			mkdir($curr_dir);
		}
	}
	
	if (!class_exists($fullclass) && !interface_exists($fullclass))
	{
		$filename = SAMARA_CACHE_DIR.$dir.'/'.$class.'.php';
		file_put_contents($filename, '<?php '."\r\n\r\n".$contents);
		//echo $filename."\n";//$filename."\n";
		include_once $filename;
		//echo (file_exists($filename) ? ' YES' : ' NO')."\n";
		//echo $fullclass.(class_exists($fullclass) ? ' YES' : ' NO')."\n";
	}
}

function Samara_ClassExists($class, $dir)
{
	global $samara_namespace;
	
	$full_class = $samara_namespace.'\\'.$class;
	if (class_exists($full_class) || interface_exists($full_class))
	{
		return true;
	}
	$filename = $dir.'/'.$class.'.sphp';
	if (file_exists(SAMARA_ROOT.$filename))
	{
		return true;
	}
	return !!(glob(SAMARA_ROOT.str_replace('inc/', 'extensions/*/', $filename)));
}

function Samara_Include($class, $dir)
{
	global $samara_namespace;
	
	//$class = Samara_GetClassName($class);
	
	$full_class = SAMARA_PREFIX.$class;//$samara_namespace.'\\'.$class;
	if (class_exists($full_class) || interface_exists($full_class))
	{
		return;
	}
	
	//$namespace = $samara_namespace ? 'namespace '.$samara_namespace.'; ' : '';
	
	$overrides = Samara_GetAroundMods($class);
	
	$filename = $dir.'/'.$class;
	
	if (!$overrides)
	{
		$file = Samara_GetFile($filename);
		//Samara_IncludeContents($namespace.$file, $samara_namespace, $class, $dir);
		Samara_IncludeContents($file, $class, $dir);
		return;
	}

	//throw new Exception($class);
	
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
		Samara_IncludeContents($file, $new_class, $dir);
		$parent = $new_class;
		$count--;
	}
}

function Samara_LoadExtensions()
{
	foreach (glob(SAMARA_ROOT.'extensions/init.php') as $include)
	{
		require_once $include;
	}
}
