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
	$prefix = '';//Samara_GetPrefix();
	//throw new Exception($old_class.' :: '.$new_class);
	$result = preg_replace('/(abstract\s*)?(class\s*)'.$old_class.'(\s*extends\s*[^\s]*)?(\s*implements\s*[^\{]*)?(\s*\{)/', "\r\n\r\n".($is_first ? '$1' : 'abstract ').'$2'.$prefix.$new_class.($parent ? ' extends '.$prefix.$parent : '$3').'$4$5', $result);
	//$result = preg_replace('/(?<!~)~(?!~)/', SAMARA_PREFIX.'_', $result);
	//$result = str_replace('~~', '~', $result);
	
	/*if ($prefix)
	{
		static $classes = array();
		$classes[$old_class] = $prefix.$old_class;
		
		foreach ($classes as $old => $new)
		{
			$result = preg_replace('/\\b(?<!~)'.$old.'\\b/', $new, $result);
			$result = preg_replace('/\\b~'.$old.'\\b/', $old, $result);
		}
	}*/
		
	return $result;
}

function Samara_IncludeContents($contents, $class, $dir)
{
	$fullclass = Samara_FullClass($class);//$namespace.'\\'.$class;
	//$cache_dir = SAMARA_CACHE_DIR;//($samara_include_method == SAMARA_TEST ? SAMARA_ROOT.'Tests/class_cache/'.$namespace.'/' : SAMARA_ROOT.'class_cache/');
	$parts = explode('/', $dir);
	$cache_dir = Samara_CacheDir();
	$curr_dir = $cache_dir;
	if (!is_dir($curr_dir))
	{
		if (!is_dir(dirname($curr_dir)))
		{
			mkdir(dirname($curr_dir));
		}
		mkdir($curr_dir);
	}
	foreach ($parts as $part)
	{
		if (!is_dir($curr_dir .= "/$part"))
		{
			mkdir($curr_dir);
		}
	}
	
	$filename = $cache_dir.$dir.'/'.$class.'.php';
	if (!class_exists($fullclass) && !interface_exists($fullclass))
	{
		
		if (!file_exists($filename) || SAMARA_BUILD == SAMARA_DEV)
		{
			$contents = preg_replace('/\\b(interface\\s+)(\\w+[\\s|\\{])/', '$1'.Samara_GetPrefix().'$2', $contents);
			$contents = preg_replace('/\\b(class\\s+)(\\w+)\\b/', '$1'.Samara_GetPrefix().'$2', $contents);
			$contents = preg_replace('/(\\s+class\\s+\\w+\\s+extends\\s+)(\\w+)\\b/', '$1'.Samara_GetPrefix().'$2', $contents);
			$contents = preg_replace('/(\\s+class\\s+\\w+\\s+)(extends\\s+\\w+\\s+)?(implements\\s+)(\\w+)\\b/', '$1$2$3'.Samara_GetPrefix().'$4', $contents);
			$contents = preg_replace('/\\b(new\\s+)(\\w+)\\b/', '$1'.Samara_GetPrefix().'$2', $contents);
			$contents = preg_replace('/\\b(?<![\\\\|\\$])([A-Z]\\w+)::/', Samara_GetPrefix().'$1::', $contents);
			$contents = preg_replace('/\\b(new\\s+)\\\\(\\w+)\\b/', '$1$2', $contents);
			$contents = preg_replace('/\\b\\\\(\\w+)::\\b/', '$1::', $contents);
			$contents = preg_replace('/([\(|\,]\s*)(?<![\\\\|\\$])(\w+)(\s+\$\w+)/', '$1'.Samara_GetPrefix().'$2$3', $contents);
			file_put_contents($filename, '<?php '."\r\n\r\n".$contents);
			//echo $filename.(file_exists($filename) ? ' YES' : ' NO')."\n";
		//echo $filename."\n";//$filename."\n";
			include_once $filename;
		}
		//$rf = get_included_files();
		//echo (file_exists($filename) ? ' YES' : ' NO')."\n";
		//echo $fullclass.(class_exists($fullclass) ? ' YES' : ' NO')."\n";
		if (!class_exists($fullclass) && !interface_exists($fullclass))
		{
			//die($contents);
			//require $filename;
			//echo $fullclass.(class_exists($fullclass) ? ' YES' : ' NO')."\n";
			//print_r(get_included_files());//$rf);//get_required_files());// get_included_files());
			//$x = array_search($filename, $rf);
			//echo $x ?: 'FALSE'."\n";
			//for ($i = 0; $i < count($rf) && $i < 71; $i++)
			//{
			//	echo $rf[$i]."\n";
			//}
			//die();//$x);
		}
	}
}

function Samara_GetExtensionDir()
{
	if (SAMARA_BUILD == SAMARA_TEST)
	{
		global $extension_dir;
		return $extension_dir;
	}
	return SAMARA_EXTENSIONS_DIR;
}

function Samara_GetPrefix()
{
	if (SAMARA_BUILD == SAMARA_TEST)
	{
		global $samara_test_id;
		return $samara_test_id.'_';
	}
	return SAMARA_PREFIX;
}

function Samara_FullClass($class)
{
	return Samara_GetPrefix().Samara_RemovePrefix($class);
}

function Samara_CacheDir()
{
	if (SAMARA_BUILD == SAMARA_TEST)
	{
		global $samara_test_class, $samara_test_name;
		return SAMARA_CACHE_DIR.$samara_test_class.'/'.$samara_test_name.'/';
	}
	return SAMARA_CACHE_DIR;
}

function Samara_ClassExists($class, $dir)
{
	//global $samara_namespace;
	$full_class = Samara_FullClass($class);
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
	
	$full_class = Samara_FullClass($class);//SAMARA_PREFIX.$class;//$samara_namespace.'\\'.$class;
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
	foreach (glob(Samara_GetExtensionDir().'*/init.php') as $include)
	{
		require_once $include;
	}
}
