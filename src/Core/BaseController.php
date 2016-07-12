<?php
/**
 *
 * @Controller Interface
 *
 */

namespace TinyFw\Core;

interface IController {}

abstract class BaseController implements IController
{
    protected $_registry , $_layout_path = NULL , $_children = array();
	
	public function __construct()
	{
        $this->_registry = FrontController::getInstance()->getRegistry();

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
	
	protected function renderView($path, $layout_path = NULL)
	{
		if (!is_null($layout_path))
			$this->_layout_path = $layout_path;
		
		foreach ($this->_children as $child) {
			$param_name = str_replace("-", "_", $child->getAction());
            $this->oView->{$param_name} = Request::staticRun($child);
		}

        return $this->oView->renderLayout($path, $this->_layout_path);
	}

}
