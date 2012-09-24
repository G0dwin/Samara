<?php 

abstract class QueryPart extends SamaraBase
{

	abstract function Compile();//$table_name_is_required = TRUE);
	
	public function __call($name, $args)
	{
		if (array_search(strtolower($name), array('abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor')))
		{
			return call_user_func_array(array($this, '_'.$name), $args);
		}
		$class = get_class($this);
		$trace = debug_backtrace();
		$file = $trace[0]['file'];
		$line = $trace[0]['line'];
		trigger_error("Call to undefined method $class::$name() in $file on line $line", E_USER_ERROR);
	}
	
	public static function IsA($obj)
	{
		return $obj !== NULL && is_a($obj, get_called_class());
	}
	
}

