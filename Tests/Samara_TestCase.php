<?php

require_once 'PHPUnit\Framework\TestCase.php';
require_once '\settings.php';
require_once '\inc\include.php';
require_once '\inc\modules.php';

/**
 * test case.
 */
abstract class Samara_TestCase extends PHPUnit_Framework_TestCase
{
	
	public $namespace;
	public $class_under_test;
	public $file_location;
	public $always_include;
	public $reset_modules;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		global $samara_include_method, $samara_modules, $samara_namespace;
		$samara_include_method = SAMARA_TEST;
		if ($this->reset_modules !== FALSE)
		{
			$samara_modules = array();
		}
		$samara_namespace = $this->getName().__CLASS__;
		$this->namespace = $samara_namespace;
		if ($this->always_include)
		{
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
		$reflector = new ReflectionClass($this->GetClass($class));
		return $reflector->newInstanceArgs($args);
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
		return $this->namespace.'\\'.($class ?: $this->class_under_test);
	}
	
	protected function NewTestObject()
	{
		return $this->Create($this->class_under_test, func_get_args() ?: array());
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct()
	{
		$this->class_under_test = preg_replace('/^(.*\\\\)?(.*)(Test)$/', '$2', get_called_class());
		$this->file_location = 'inc';
		$this->always_include = true;
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
