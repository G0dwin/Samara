<?php Samara_Include('Theme', 'inc');

class Controller
{

	protected static $current_instance;
	protected $params;
	
	protected function __construct()
	{
		
	}
	
	public function call($name, $args)
	{
		if (preg_match('/^render(.+)$/', $name, $matches))
		{
			return Theme::GetView(NULL, $matches[1]);
		}
	}
	
	public static function __callStatic($name, $args)
	{
		$called_class = get_called_class();
		$instance = Controller::GetInstance();
		$exists = method_exists($instance, $name);
		$class = get_class($instance);
		if ($exists && $name[0] >= 'A' && $name[0] <= 'Z')
		{
			return call_user_func_array(array($called_class == Samara_GetClassName('Controller') ? $instance : new $called_class(), $name), $args);
		}
		if ($exists)
		{
			throw new \Exception("The method $class::$name is not public");
		}
		throw new \Exception("The method $class::$name does not exist");
	}
	
	protected function GetControllerList()
	{
		$files = glob(SAMARA_ROOT.$this->GetControllerDir().'/*Controller.sphp');
		$classes = array('Controller');
		foreach ($files as $file)
		{
			$class = preg_replace('/^(.*[\/|\\\\])?([^\/|^\\\\]*)\.sphp$/', '$2', $file);
			$classes[] = $class;
			Samara_Include($class, $this->GetControllerDir());
		}
		return $classes;
	}
	
	protected function GetComponents()
	{
		$components = '';
		foreach ($this->GetControllerList() as $controller)
		{
			$c = new $controller();
			$components .= $c->components();
		}
		return $components;
	}
	
	protected function components()
	{
		return '';
	}
	
	protected function GetControllerDir()
	{
		return '/composition/controllers';
	}
	
	protected static function GetInstance()
	{
		return Controller::$current_instance ?: (Controller::$current_instance = new Controller());
	}
	
	protected function Render()
	{
		$params = $this->params = $this->getParams();
		$controller = $this->GetControllerName();
		$class = $controller.'Controller';
		//print $class;
		if ($controller && Samara_ClassExists($class, 'composition/controllers'))
		{
			Samara_Include($class, 'composition/controllers');
			$class = Samara_GetFullClassName($class);
			Controller::$current_instance = new $class();
			Controller::$current_instance->params = $params;
		}
		print Controller::$current_instance->CreatePage();
	}
	
	public function CreatePage()
	{
		$page = $this->GetPageName();
		$method = "render$page";
		return Theme::Process($this->$method());
	}
	
	public function renderIndex()
	{
		return Theme::getView();
	}
	
	protected function GetControllerName()
	{
		return $this->Param('controller');
	}
	
	protected function GetPageName()
	{
		return $this->GetAction();
	}
	
	protected function GetAction()
	{
		return $this->Param('action') ?: 'Index';
	}
	
	protected function Param($name)
	{
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}
	
	protected function getParams()
	{
		$request = preg_split('/[\/|\?]/', trim($_SERVER['REQUEST_URI'], '/'));
		$params = array('controller' => array_shift($request), 'action' => array_shift($request));
		$params['params'] = array();
		foreach ($request as $param)
		{
			$p = explode('=', $param);
			if (count($p) > 1)
			{
				$params[$p[0]] = $p[1];
			}
			else
			{
				$params['params'][] = $param;
			}
		}
		$params = array_merge($params, $_GET, $_POST);
		return $params;
	}
	
	protected static function redirect($action = NULL, $params = NULL)
	{
		$class = get_called_class();
		$instance = Controller::$current_instance = new $class();
		if ($class != get_class(Controller::GetInstance()))
		{
			return $instance->redirect($action, $params);
		}
			
		$params['action'] = $action;
		$params['controller'] = $instance->GetControllerName();
		$instance->params = $params;
		
		$render = 'render'.$instance->GetPageName();
		return $instance->$render();
	}
	
}

