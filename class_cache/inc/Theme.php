<?php 

Samara_Include('SamaraBAse', 'inc');

class Theme extends SamaraBase
{

	protected static $instance;
	protected $last_view_name;
	protected $last_controller;
	protected $last_params;
	protected $css_files;
	protected $page_template;
	
	protected function __construct()
	{
		
	}
	
	public static function __callStatic($name, $args)
	{
		return call_user_func_array(array(Theme::GetInstance(), $name), $args);
	}
	
	protected static function GetInstance()
	{
		global $samara_theme;
		$theme = $samara_theme.'Theme';
		Samara_Include($theme, 'themes/'.Samara_ToUnderscoreCase($samara_theme));
		//$theme = Samara_GetFullClassName($theme);
		return Theme::$instance ?: (Theme::$instance = new $theme());
	}
	
	protected function ProcessMenus($menus)
	{
		$positions = $this->GetMenuPositions();
		foreach ($menus as $menu)
		{
			$name = $menu->GetName();
			if (isset($positions[$name]))
			{
				$menu->SetAttribute('position', $positions[$name]);
			}
		}
	}
	
	protected function SetPageTemplate($xml)
	{
		$this->page_template = $xml;
	}
	
	protected function SetPageTemplateFile($xml_file)
	{
		$this->page_template = $this->GetXmlContents($xml_file);
	}
	
	protected function GetMenuPositions()
	{
		return array('primary' => 'header-bottom', 'secondary' => 'sidebar-left', 'admin' => 'header-top');
	}
	
	protected function getCurrentView()
	{
		return $this->last_view_name;
	}
	
	protected function getCurrentController()
	{
		return $this->last_controller;
	}
	
	protected function getCurrentParams()
	{
		return $this->last_params;
	}
	
	protected function GetView($params = NULL, $view = NULL, $controller = NULL)
	{
		$this->last_params = $params;
		if ($view === NULL)
		{
			$view = Controller::GetPageName();
		}
		$view = Samara_ToUnderscoreCase($view);
		$this->last_view_name = $view;
		if ($controller === NULL)
		{
			$controller = Controller::GetControllerName();
		}
		$this->last_controller = $controller;
		$filename = $this->getFile(($controller ? $controller.'/' : '').'default.view') ?: $this->getFile('default.view');
		return $this->addXmlHeader($this->GetXmlContents($filename, $params));
	}
	
	protected function addXmlHeader($xml)
	{		
		return '<?xml version="1.0" encoding="ISO-8859-1"?>'.$xml;
	}
	
	protected function addXslHeader($xsl)
	{
		return $this->addXmlHeader($xsl);//$this->addXmlHeader('<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="yes" />'.$xsl.'</xsl:stylesheet>');
	}
	
	protected function RenderComponent($name)
	{
		switch ($name)
		{
			case 'page':
				$filename = $this->getFile(($this->last_controller ? $this->last_controller.'/' : '').$this->last_view_name.'.view', SAMARA_ROOT.'/inc/views');
				if (!file_exists($filename))
				{
					if ($this->page_template)
					{
						return $this->compilePHPTags($this->page_template);
					}
					return $this->Get404View();
				}
				return $this->GetXmlContents($filename, $this->last_params);
			case 'sidebar':
				return '';
			case 'css-files':
				$files = $this->getCSSFiles();
				if (count($files) > 0)
				{
					return '<css-file>'.implode('</css-file><css-file>', $files).'</css-file>';
				}
				return '';
			case 'js-files':
				$files = $this->getJSFiles();
				if (count($files) > 0)
				{
					return '<js-file>'.implode('</js-file><js-file>', $files).'</js-file>';
				}
				return '';
			case 'app-icon':
				return '<app-icon>'.($this->getFile('logo-small.png') ?: $this->getFile('logo.png')).'</app-icon>';
			case 'components':
				return Controller::GetComponents();
			case 'menus':
				return MenuController::GetMenus();
		/*$controllers = Controller::GetControllerList();
		
		foreach ($controllers as $controller)
		{
			$controller = Samara_GetFullClassName($controller);
			print($controller);
			print(' : ');
			echo '-'.$controller::ControllerName().'-';
			print('<br />');
		}
		die();
				break;*/
		}
	}
	
	protected function Get404View()
	{
		return $this->GetView(NULL, '404', '');
	}
	
	protected function GetThemeName()
	{
		return preg_replace('/^\_*(.*)Theme$/', '$1', get_class(Theme::$instance));
	}
	
	function GetXmlContents($filename, $params = NULL)
	{
		if (is_file($filename))
		{
			if ($params)
			{
				foreach ($params as $var => $value)
				{
					$$var = $value;
				}
			}
			ob_start();
			include $filename;
			return ob_get_clean();
		}
		return false;
	}
	
	function GetXslContents($filename, $params = NULL)
	{
		$contents = FALSE;
		/*if (is_array($filename))
		{
			$contents = '';
			foreach ($filename as $file)
			{
				$contents .= $this->GetXmlContents($file, $params, '');
			}
		}
		else
		{*/
		$contents = $this->GetXmlContents($filename, $params, '');
		//}
		if ($contents !== FALSE)
		{
			$contents = $this->addXslHeader($contents);
		}
		return $contents;
	}
	
	protected function RenderLayout($layout)
	{
		print $this->Process($this->last_view, $layout);
	}
	
	protected function Process($xml, $layout = NULL)
	{
		$this->last_view = $xml;
		$xslt = new XSLTProcessor();
		$dirs = $this->getDirs();
		$files = array();
		$filename = ($layout === NULL ? 'default' : $layout).'.layout';
		$xsl = $this->GetXslContents($this->getFile($filename));
		
		$xslt->importStylesheet(new SimpleXMLElement($xsl));
		$x = $xslt->transformToXml(new SimpleXMLElement($xml));
		
		return $this->compilePHPTags($x);
	}
	
	protected function compilePHPTags($html_code_with_php_tags_ABC234)
	{
		if ($this->last_params)
		{
			foreach ($this->last_params as $var => $value)
			{
				$$var = $value;
			}
		}
		
		return eval('?> '.$html_code_with_php_tags_ABC234.' <?php ');
	}
	
	protected function getFile($name, $default = NULL)
	{
		$filename = NULL;
		$dirs = $this->getDirs();

		while ($dirs)
		{
			$filename = array_shift($dirs).'/'.$name;
			//echo SAMARA_ROOT.$filename.'<br />';
			if (file_exists(SAMARA_ROOT.$filename))
			{
				//echo '['.$filename.']';
				return $filename;
			}
		}
		return $default ? $default.'/'.$name : null;
	}
	
	public function GetThemeDir()
	{
		return 'themes/'.Samara_ToUnderscoreCase($this->GetThemeName());
	}
	
	protected function getCSSFiles()
	{
		if (Theme::IsA(get_parent_class()))
		{
			$files = parent::getCSSFiles();
		}
		else
		{
			$files = array();
		}
		$filename = $this->GetThemeDir().'/'.Samara_ToUnderscoreCase($this->GetThemeName()).'.css';
		if (file_exists(SAMARA_ROOT.$filename))
		{
			$files[] = '/'.$filename;
		}
		return $files;
	}
	
	protected function getJSFiles()
	{
		if (Theme::IsA(get_parent_class()))
		{
			$files = parent::getJSFiles();
		}
		else
		{
			$files = array();
		}
		$filename = $this->GetThemeDir().'/'.Samara_ToUnderscoreCase($this->GetThemeName()).'.js';
		if (file_exists(SAMARA_ROOT.$filename))
		{
			$files[] = '/'.$filename;
		}
		return $files;
	}
	
	protected function getDirs()
	{
		$dirs = array();
		$class = get_called_class();
		while ($class != FALSE)
		{
			$dir = $this->GetThemeDir();
			if (!array_search($dir, $dirs))
			{
				$dirs[] = $dir;
			}
			$class = get_parent_class($class);
		}
		return $dirs;
	}
	
	protected function GetLogo()
	{
		return '/'.$this->getFile('logo.png');
	}
	
}

Samara_Include('MenuController', 'inc/controllers');
