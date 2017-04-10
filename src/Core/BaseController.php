<?php
/**
 *
 * @Controller Interface
 *
 */

namespace TinyFw\Core;

interface OLD_IController {}

abstract class BaseController implements OLD_IController
{
    protected $_registry , $_layout_path = NULL , $_children = array();
	
	public function __construct()
	{
        $this->_registry = Registry::getInstance();

		// --- Set oView Params ---//
		$this->oView->oConfig = $this->oConfig;
	}
	
	public function __get($key) 
	{
		return $this->_registry->{$key};
	}
	
	public function __set($key, $value) 
	{
		$this->_registry->{$key} = $value;
	}

	protected function forward($route, $args = array()) 
	{
		return new Request($route, $args);
	}
	
	protected function renderView($path, $variables = array(), $layout_path = NULL)
	{
		if (!is_null($layout_path))
			$this->_layout_path = $layout_path;

        //-- Set variables for view --
        $this->oView->setVars($variables);

		foreach ($this->_children as $child) {
			$param_name = str_replace("-", "_", $child->getAction());
            $this->oView->{$param_name} = Request::staticRun($child);
		}

        return $this->oView->renderLayout($path, $this->_layout_path);
	}

}
