<?php
/**
 *
 * @Controller Interface
 *
 */

namespace TinyFw\Core;

use TinyFw\Support\Request as RequestSupport;

interface IController {}

abstract class Controller extends Container implements IController
{
    protected $_layout_path = NULL , $_children = array();
	
	public function __construct()
	{
		// --- Set oView Params ---//
		$this->oView->oConfig = $this->oConfig;
	}
	
	final public function __get($key)
	{
		return parent::get($key);
	}
	
	final public function __set($key, $value)
	{
	    parent::set($key, $value);
	}

	protected function forward($route, $args = array(), $namespace = NULL)
	{
		return new Request($route, $args, $namespace);
	}
	
	protected function renderView($path, $variables = array(), $layout_path = NULL)
	{
		if (!is_null($layout_path))
			$this->_layout_path = $layout_path;

        //-- Set variables for view --
        $this->oView->setVars($variables);

		foreach ($this->_children as $child) {
			$param_name = str_replace("-", "_", $child->getAction());
            $this->oView->{$param_name} = RequestSupport::run($child);
		}

        return $this->oView->renderLayout($path ,null ,$this->_layout_path);
	}
}
