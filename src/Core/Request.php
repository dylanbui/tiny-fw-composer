<?php

namespace TinyFw\Core;

final class Request 
{
	protected $class;
	protected $method;
	protected $moduleNamespace;

    // -- Default value --
    protected $module = 'index';
	protected $controller = 'index';
	protected $action = 'index';
    protected $args = array();

	public function __construct($route  = 'index/index/index', $args = array(), $moduleNamespace = NULL)
	{
		$this->parseUri($route);

        // -- Default $moduleNamespace get form FrontController --
        if (is_null($moduleNamespace))
		    $this->moduleNamespace = FrontController::getInstance()->getDefaultControllerNamespace();
        else
            $this->moduleNamespace = $moduleNamespace;

        $this->moduleNamespace .= '\\'.$this->upperCamelcase($this->module).'\\';
		$this->class = $this->moduleNamespace.$this->upperCamelcase($this->controller).'Controller';
		$this->method = $this->lowerCamelcase($this->action).'Action';
		$this->args = array_merge($this->args,$args);
	}
	
	private function parseUri($route)
	{
		// removes the trailing slash
// 		/this/that/theother/ => this/that/theother
		$route = trim($route, '/');
		$parts = explode('/', str_replace('../', '', $route));

        $module = array_shift($parts);
        if(empty($module))
            return;
        $this->module = $module;

        $controller = array_shift($parts);
        if(empty($controller))
            return;
        $this->controller = $controller;

        $action = array_shift($parts);
        if(empty($action))
            return;
        $this->action = $action;
        $this->args = $parts;
	}

	public function getClass() {
		return $this->class;
	}
	
	public function getMethod() {
		return $this->method;
	}

    public function setArgs($args) {
        $this->args = $args;
    }

	public function getArgs() {
		return $this->args;
	}
	
	public function getRouter()	{
		return "{$this->module}/{$this->controller}/$this->action";		
	}
	
	public function getModule() {
		return $this->module;
	}

	public function getController() {
		return $this->controller;
	}

	public function getAction() {
		return $this->action;
	}

    // -- Fixed DucBui : 24/11/2015  --
    public static function staticRun($request)
    {
        if(!$request instanceof Request)
            $request = new Request($request);

        return $request->run();
    }

    public function run()
    {
        $class  = $this->getClass();
        $method = $this->getMethod();
        $args   = $this->getArgs();

        try {
            $rc = new \ReflectionClass($class);
            if($rc->isSubclassOf(__NAMESPACE__.'\BaseController'))
            {
                $controller = $rc->newInstance();
                $classMethod = $rc->getMethod($method);
                return $classMethod->invokeArgs($controller,$args);
            }
            else {
            	throw new \Exception("abstract class BaseController must be extended");
            }
        }
        catch (\ReflectionException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

	//// underscored to upper-camelcase 
	//// e.g. "this_method_name" -> "ThisMethodName" 
	private function upperCamelcase($string)
	{
        // -- User for php 5.6 -> 7 --
        return preg_replace_callback(
            '/(?:^|-)(.?)/',
            function($match) { return strtoupper($match[1]); },
            $string
        );
    }

	//// underscored to lower-camelcase 
	//// e.g. "this_method_name" -> "thisMethodName" 
	private function lowerCamelcase($string)
	{
        // -- User for php 5.6 -> 7 --
        return preg_replace_callback(
            '/-(.?)/',
            function($match) { return strtoupper($match[1]); },
            $string
        );
	}	
}
?>