<?php

/**
 *
 * @Dispatcher Controller class
 *
 * @package Core
 *
 */

namespace TinyFw\Core;

class Dispatcher
{
    private $patterns = array(
        ':name'     => '[a-z\-]+',
        ':num'      => '[0-9]+',
        ':slug'     => '[A-Za-z-0-9\-]+',
        ':other'    => '[/]{0,1}[A-Za-z0-9\-\\/\.]+', // => maybe same (:any)
        ':any'      => '.+',
        ':extant'   => '[/]{0,1}.+',
    );

    private $segments = array();

    protected $_defaultUri = 'index/index';
    protected $_controllerNamespace = 'App\Controller';
    protected $_currentRequest;
    protected $_routes = array();
	protected $_preRequest = array();
    protected $_postRequest = array();

	public function __construct($controllerNamespace)
    {
        $this->_controllerNamespace = $controllerNamespace;
    }

    public function getCurrentRequest()
    {
        return $this->_currentRequest;
    }

    public function setControllerNamespace($namespace)
    {
        $this->_controllerNamespace = $namespace;
    }

    public function getControllerNamespace()
    {
        return $this->_controllerNamespace;
    }

    public function getRoutes()
    {
        return $this->_routes;
    }

    public function setRoutes($routes)
    {
        return $this->_routes = $routes;
    }

    public function getDefaultUri()
    {
        return $this->_defaultUri;
    }

    public function setDefaultUri($uri)
    {
        return $this->_defaultUri = $uri;
    }

	public function addPreRequest(Request $preRequest)
	{
		$this->_preRequest[] = $preRequest;
	}

    public function addPostRequest(Request $postRequest)
    {
        $this->_postRequest[] = $postRequest;
    }

	public function send()
	{
        // -- Load URL --
        $uri = empty($_GET['_url']) ? $this->_defaultUri : $_GET['_url'];
        $this->segments['_url'] = '/'.str_replace(array('//', '../'), '/', trim($uri, '/'));
        $this->segments['_namespace'] = $this->_controllerNamespace;

//        echo "<pre>";
//        print_r($this->_routes);
//        echo "</pre>";

		// Load pre config router
        // Loop through the route array looking for wild-cards
        if(!empty($this->_routes)) // array();
            $this->loadPreRouter($this->_routes);

//        echo "<pre>";
//        print_r($this->segments);
//        echo "</pre>";
//        exit();

        // -- Load current request --
        $this->_currentRequest = new Request($this->segments['_url'], array(), $this->segments['_namespace']);
        // -- Khong su dung _url_params lam params --
//        if (!empty($this->segments['_url_params']))
//            $this->_currentRequest->setArgs($this->segments['_url_params']);

        // -- Save current Request to Register --
        Container::$_container['oRequest'] = $this->_currentRequest;

        // -- Loop pre request --
		$request = $this->_currentRequest;
		foreach ($this->_preRequest as $preRequest)
		{
            $result = $preRequest->run();
			if ($result)
			{
				$request = $result;
				break;
			}
		}

        // -- Run Request --
        while ($request instanceof Request) {
            $request = $request->run();
		}

        // -- Loop post request --
        foreach ($this->_postRequest as $postRequest)
        {
            $postRequest->run();
        }
	}

    private function loadPreRouter($routes)
    {
        $uri = trim($this->segments['_url'],'/');

        // Get HTTP verb
        $http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

        foreach ($routes as $key => $val)
        {
            // Check if route format is using HTTP verbs
            if (is_array($val))
            {
                $val = array_change_key_case($val, CASE_LOWER);
                if (isset($val[$http_verb]))
                {
                    $val = $val[$http_verb];
                }
            }

            // -- Thong tin router la 1 array --
            if (!is_array($val))
                $val = array('path' => $val, 'namespace' => $this->_controllerNamespace);

            // Convert wildcards to RegEx
            $key = str_replace(array_keys($this->patterns), array_values($this->patterns), $key);

            // Does the RegEx match?
            if (preg_match('#^'.$key.'$#', $uri, $matches))
            {
                // Remove the original string from the matches array.
                array_shift($matches);

                // Are we using callbacks to process back-references?
                if ( ! is_string($val['path']) && is_callable($val['path']))
                {
                    // Execute the callback using the values in matches as its parameters.
                    $result = call_user_func_array($val['path'], $matches);
                    if (is_array($result))
                        $val = $result;
                    else
                        $val['path'] = $result;
                }
                else if (strpos($val['path'], '$') !== FALSE AND strpos($key, '(') !== FALSE) // Do we have a back-reference?
                {
                    $val['path'] = preg_replace('#^'.$key.'$#', $val['path'], $uri);
                }

                // -- Save namespace --
                $this->segments['_namespace'] = $val['namespace'];
                // -- Save path --
                $this->segments['_url'] = $val['path'];
                // -- Matches as its parameters --
                $this->segments['_url_params'] = $matches;

                // -- Lay cai match dau tien --
                return;
            }
        }
    }


} // end of class
