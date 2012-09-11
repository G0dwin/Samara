<?php class Theme
{

	protected static $instance;
	protected $last_view_name;
	protected $last_controller;
	protected $last_params;
	protected $css_files;
	
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
		return Theme::$instance ?: (Theme::$instance = new $theme());
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
		return $this->addXmlHeader('<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="yes" />'.$xsl.'</xsl:stylesheet>');
		//return $this->addXmlHeader('<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" version="5.0" encoding="iso-8859-1" indent="yes" />'.$xsl.'</xsl:stylesheet>');
	}
	
	protected function RenderComponent($name)
	{
		switch ($name)
		{
			case 'page':
				$filename = $this->getFile(($this->last_controller ? $this->last_controller.'/' : '').$this->last_view_name.'.view', SAMARA_ROOT.'/composition/views');
				if (!file_exists($filename))
				{
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
			case 'components':
				return Controller::GetComponents();
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
		if (is_array($filename))
		{
			$contents = '';
			foreach ($filename as $file)
			{
				$contents .= $this->GetXmlContents($file, $params, '');
			}
		}
		else
		{
			$contents = $this->GetXmlContents($filename, $params, '');
		}
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
		foreach ($dirs as $dir)
		{
			$files[] = $this->getFile($filename);
		}
		$xsl = $this->GetXslContents(array_reverse($files));
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
		//print_r($dirs);print '<br />';
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
		if (get_parent_class() !== false)
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
