<?php

namespace TinyFw\Core;

class Input 
{	
	public $_get = array();
	public $_post = array();
	public $_request = array();
	public $_files = array();
	
	public function __construct() 
	{
		$_GET = $this->clean($_GET);
		$_POST = $this->clean($_POST);
		$_REQUEST = $this->clean($_REQUEST);
		$_FILES = $this->clean($_FILES);
	
		$this->_get = &$_GET;
		$this->_post = &$_POST;
		$this->_request = &$_REQUEST;
		$this->_files = &$_FILES;
		
		$this->_cookie = &$_COOKIE;		
		$this->_server = &$_SERVER;
	}
	
	public function clean($data) 
	{
		if (is_array($data)) 
		{
			foreach ($data as $key => $value) 
			{
				unset($data[$key]);
				$data[$this->clean($key)] = $this->clean($value);
			}
		} else {
 			$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
		}
	
		return $data;
	}

    public function isAjax()
    {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    public function isPost()
    {
        return !empty($_POST);
    }

    public function requestAll()
    {
        return $this->_request;
    }

    public function request($name,$default_value = NULL)
    {
        if (!isset($this->_request[$name]) || $this->_request[$name] == "")
        {
            return $default_value;
        }
        return $this->_request[$name];

    }

	public function post($name,$default_value = NULL)
	{
		if (!isset($this->_post[$name]) || $this->_post[$name] == "")
		{
			return $default_value;
		}
		return $this->_post[$name];
	}

	public function get($name,$default_value = NULL)
	{
		if (!isset($this->_get[$name]) || $this->_get[$name] == "")
		{
			return $default_value;
		}
		return $this->_get[$name];
	}

    public function file($name)
    {
        return (isset($this->_files[$name])) ? $this->_files[$name] : null;
    }

}

?>