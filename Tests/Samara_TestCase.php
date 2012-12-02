<?php

define('SAMARA_TEST',	0x1);	// For unit testing: least secure, uses eval()
define('SAMARA_DEV',	0x2);	// For development: always recompiles cache
define('SAMARA_PROD',	0x3);	// For production: comiples when out of date

define('SAMARA_ROOT', dirname(__DIR__).'/');
define('SAMARA_BUILD', SAMARA_TEST);
define('SAMARA_CACHE_DIR', __DIR__.'/class_cache/');
define('SAMARA_EXTENSIONS_DIR', SAMARA_ROOT.'extensions/');
define('SAMARA_PREFIX', '');

require_once 'PHPUnit\Framework\TestCase.php';
require_once '\settings.php';
require_once '\inc\include.php';
require_once '\inc\modules.php';

abstract class Samara_TestCase extends PHPUnit_Framework_TestCase
{
	
	//public $namespace;
	public $class_under_test;
	public $file_location;
	public $always_include;
	public $reset_modules;
	public $class_cache_dir;
	
	
	protected static function emptyDir($dir)
	{
		return is_file($dir) ? @unlink($dir): array_map('Samara_TestCase::rmDir', glob($dir.'/*'));
	}
	
	protected static function rmDir($dir)
	{
		return is_file($dir) ? @unlink($dir): array_map('Samara_TestCase::rmDir', glob($dir.'/*'))==@rmdir($dir);
	}
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();

		//define('SAMARA_CACHE_DIR', __DIR__.'/class_cache/'.get_class($this).'/'.$this->getName().'/');
		global $samara_test_name, $samara_test_class, $samara_test_id;
		static $s_samara_test_id = 0;
		$samara_test_class = get_class($this);
		$samara_test_name = $this->getName();
		//if ($samara_test_id)
		//{
			//die($samara_test_id);
		//}
		$s_samara_test_id = $s_samara_test_id ? $s_samara_test_id + 1 : 1;
		$samara_test_id = $samara_test_class.$s_samara_test_id;
		
		if (is_dir(Samara_CacheDir()))
		{
			Samara_TestCase::rmDir(Samara_CacheDir());
		}
		//die(Samara_CacheDir());
		if (!is_dir(dirname(Samara_CacheDir())))
		{
			mkdir(dirname(Samara_CacheDir()));
		}
		mkdir(Samara_CacheDir());
				
		//global $samara_include_method, $samara_modules;//, $samara_namespace;
		//global $samara_modules;//, $samara_namespace;
		//$samara_include_method = SAMARA_TEST;
		/*if ($this->reset_modules !== FALSE)
		{
			$samara_modules = array();
		}*/
		//$samara_namespace = $this->getName().__CLASS__;
		//$this->namespace = $samara_namespace;
		
		if ($this->always_include)
		{
			//echo 'YES'."\n";
			Samara_Include($this->class_under_test, $this->file_location);
		}
	}
	
	public static function __callStatic($name, $args)
	{
		$bt = debug_backtrace();
		$obj = $bt[2]['object'];
		$class = $obj->GetClass();
		return call_user_func_array("$class::$name", $args);
	}
	
	protected function Create($class, $args)
	{
		$class = $this->GetClass($class);
		if ($args)
		{
			$reflector = new ReflectionClass($class);
			return $reflector->newInstanceArgs($args);
		}
		return new $class();
	}
	
	public function __call($name, $args)
	{
		if (preg_match('/^new\_(.*$)/', $name, $func) && class_exists($class = $this->GetClass($func[1])))
		{
			return $this->Create($func[1], $args);
		}
		return null;
	}
	
	protected function GetClass($class = NULL)
	{
		//return $this->namespace.'\\'.($class ?: $this->class_under_test);
		global $samara_test_id;
		return Samara_FullClass($class ?: $this->class_under_test);
	}
	
	protected function NewTestObject()
	{
		return $this->Create($this->GetClass(), func_get_args() ?: array());
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct()
	{
		$this->class_under_test = preg_replace('/^(.*\\\\)?(.*)(Test)$/', '$2', get_called_class());
		$this->file_location = 'inc';
		$this->always_include = true;
		global $samara_test_class;
		$samara_test_class = get_class($this);
		//$this->class_cache_dir = __DIR__.'/class_cache/'.get_class($this).'/';
		//if (!is_dir($this->class_cache_dir))
		//{
		//	mkdir($this->class_cache_dir);
		//}
		//throw new Exception(class_exists('Database') ? 'TRUE' : 'FALSE');
	}
	
	protected function assertXmlEqual(SimpleXMLElement $xml1, SimpleXMLElement $xml2)
	{
		$result = $this->xml_is_equal($xml1, $xml2);
		if ($result !== TRUE)
		{
			$this->fail($result);
		}
	}
	
	/**
	 *  Adapted from: http://www.jevon.org/wiki/Comparing_Two_SimpleXML_Documents
	 * @param SimpleXMLElement $xml1
	 * @param SimpleXMLElement $xml2
	 * @param unknown_type $text_strict
	 * @return string|boolean
	 */
	protected function xml_is_equal(SimpleXMLElement $xml1, SimpleXMLElement $xml2, $text_strict = false) {
		// compare text content
		if ($text_strict) {
			if ("$xml1" != "$xml2") return "mismatched text content (strict)";
		} else {
			if (trim("$xml1") != trim("$xml2")) return "mismatched text content";
		}
	
		// check all attributes
		$search1 = array();
		$search2 = array();
		foreach ($xml1->attributes() as $a => $b) {
			$search1[$a] = "$b";		// force string conversion
		}
		foreach ($xml2->attributes() as $a => $b) {
			$search2[$a] = "$b";
		}
		if ($search1 != $search2) return "mismatched attributes";
	
		// check all namespaces
		$ns1 = array();
		$ns2 = array();
		foreach ($xml1->getNamespaces() as $a => $b) {
			$ns1[$a] = "$b";
		}
		foreach ($xml2->getNamespaces() as $a => $b) {
			$ns2[$a] = "$b";
		}
		if ($ns1 != $ns2) return "mismatched namespaces";
	
		// get all namespace attributes
		foreach ($ns1 as $ns) {			// don't need to cycle over ns2, since its identical to ns1
			$search1 = array();
			$search2 = array();
			foreach ($xml1->attributes($ns) as $a => $b) {
				$search1[$a] = "$b";
			}
			foreach ($xml2->attributes($ns) as $a => $b) {
				$search2[$a] = "$b";
			}
			if ($search1 != $search2) return "mismatched ns:$ns attributes";
		}
	
		// get all children
		$search1 = array();
		$search2 = array();
		foreach ($xml1->children() as $b) {
			if (!isset($search1[$b->getName()]))
				$search1[$b->getName()] = array();
			$search1[$b->getName()][] = $b;
		}
		foreach ($xml2->children() as $b) {
			if (!isset($search2[$b->getName()]))
				$search2[$b->getName()] = array();
			$search2[$b->getName()][] = $b;
		}
		// cycle over children
		if (count($search1) != count($search2)) return "mismatched children count";		// xml2 has less or more children names (we don't have to search through xml2's children too)
		foreach ($search1 as $child_name => $children) {
			if (!isset($search2[$child_name])) return "xml2 does not have child $child_name";		// xml2 has none of this child
			if (count($search1[$child_name]) != count($search2[$child_name])) return "mismatched $child_name children count";		// xml2 has less or more children
			foreach ($children as $child) {
				// do any of search2 children match?
				$found_match = false;
				$reasons = array();
				foreach ($search2[$child_name] as $id => $second_child) {
					if (($r = $this->xml_is_equal($child, $second_child)) === true) {
						// found a match: delete second
						$found_match = true;
						unset($search2[$child_name][$id]);
					} else {
						$reasons[] = $r;
					}
				}
				if (!$found_match) return "xml2 does not have specific $child_name child: " . implode("; ", $reasons);
			}
		}
	
		// finally, cycle over namespaced children
		foreach ($ns1 as $ns) {			// don't need to cycle over ns2, since its identical to ns1
			// get all children
			$search1 = array();
			$search2 = array();
			foreach ($xml1->children() as $b) {
				if (!isset($search1[$b->getName()]))
					$search1[$b->getName()] = array();
				$search1[$b->getName()][] = $b;
			}
			foreach ($xml2->children() as $b) {
				if (!isset($search2[$b->getName()]))
					$search2[$b->getName()] = array();
				$search2[$b->getName()][] = $b;
			}
			// cycle over children
			if (count($search1) != count($search2)) return "mismatched ns:$ns children count";		// xml2 has less or more children names (we don't have to search through xml2's children too)
			foreach ($search1 as $child_name => $children) {
				if (!isset($search2[$child_name])) return "xml2 does not have ns:$ns child $child_name";		// xml2 has none of this child
				if (count($search1[$child_name]) != count($search2[$child_name])) return "mismatched ns:$ns $child_name children count";		// xml2 has less or more children
				foreach ($children as $child) {
					// do any of search2 children match?
					$found_match = false;
					foreach ($search2[$child_name] as $id => $second_child) {
						if ($this->xml_is_equal($child, $second_child) === true) {
							// found a match: delete second
							$found_match = true;
							unset($search2[$child_name][$id]);
						}
					}
					if (!$found_match) return "xml2 does not have specific ns:$ns $child_name child";
				}
			}
		}
	
		// if we've got through all of THIS, then we can say that xml1 has the same attributes and children as xml2.
		return true;
	}

}

