<?php

namespace TinyFw\Core;

use TinyFw\Support\Dispatcher as DispatcherSupport;

class Request
{
	protected $class;
	protected $method;
	protected $namespace;

    // -- Default value --
	protected $controller = 'index';
	protected $action = 'index';
    protected $args = array();

	public function __construct($route  = 'index/index', $args = array(), $namespace = NULL)
	{
	    // -- Parse router to controller, action --
		$this->parseUri($route);

        // -- Default namespace --
        if (is_null($namespace))
            $this->namespace = DispatcherSupport::getControllerNamespace();
        else
            $this->namespace = $namespace;

        $this->class = $this->namespace.'\\'.$this->upperCamelcase($this->controller).'Controller';
        $this->method = $this->lowerCamelcase($this->action).'Action';
		$this->args = array_merge($this->args,$args);
	}

	// -- Only get --
	public function getClass() {
		return $this->class;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getRouter()	{
		return "{$this->controller}/$this->action";
	}

	public function getController() {
		return $this->controller;
	}

	public function getAction() {
		return $this->action;
	}

    public function getArgs() {
        return $this->args;
    }

    public function setArgs($args) {
        $this->args = $args;
    }

    public function run($request = null)
    {
        if(!is_null($request))
        {
            if(!$request instanceof Request)
                $request = new Request($request);

            return $request->run();
        }

        try {
            $rc = new \ReflectionClass($this->class);
            if($rc->isSubclassOf(__NAMESPACE__.'\Controller'))
            {
                $controller = $rc->newInstance();
                $classMethod = $rc->getMethod($this->method);
                return $classMethod->invokeArgs($controller,$this->args);
            }
            else {
            	throw new \Exception("abstract class Controller must be extended");
            }
        }
        catch (\ReflectionException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    private function parseUri($route)
    {
        // removes the trailing slash
// 		/this/that/theother/ => this/that/theother
        $route = trim($route, '/');
        $parts = explode('/', str_replace('../', '', $route));

        // -- Get controller --
        $controller = array_shift($parts);
        if(empty($controller))
            return;
        $this->controller = $controller;

        // -- Get action --
        $action = array_shift($parts);
        if(empty($action))
            return;
        $this->action = $action;

        // -- Args --
        $this->args = $parts;
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