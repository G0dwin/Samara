<?php 

class Vars extends SamaraBase
{
	
	protected static $instance;
	
	protected function __construct()
	{
		session_start();
	}
	
	public static function __callstatic($name, $args)
	{
		return call_user_func_array(array(Vars::getInstance(), $args ? 'set' : 'get'), $args ? array($name, $args[0]) : array($name));
	}
	
	protected static function getInstance()
	{
		return Vars::$instance ?: (Vars::$instance = new Vars());
	}
	
	protected function get($name)
	{
		return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
	}
	
	protected function set($name, $value)
	{
		return ($_SESSION[$name] = $value);
	}

}

