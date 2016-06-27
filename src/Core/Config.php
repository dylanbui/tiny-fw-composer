<?php

namespace TinyFw\Core;

final class Config
{
	/*
	 * @var object $instance
	*/
	private static $instance = null;
		
	/*
	 * @var array $config_values; 
	 */
	public $config_values = array();

	/**
	 *
	 * Return Config instance or create intitial instance
	 *
	 * @access public
	 *
	 * @return object
	 *
	 */
	public static function getInstance()
	{
 		if(is_null(self::$instance))
 		{
 			self::$instance = new config;
 		}
		return self::$instance;
	}

	/**
	 *
	 * @the constructor is set to private so
	 * @so nobody can create a new instance using new
	 *
	 */
	private function __construct()
	{
		
	}

	/**
	 *
	 * @__clone
	 *
	 * @access private
	 *
	 */
	private function __clone() {}

    public function load($file_config)
    {
        $this->config_values = array_merge(require_once($file_config), $this->config_values) ;
    }

	/**
	 * @get a config option by key
	 *
	 * @access public
	 *
	 * @param string $key:The configuration setting key
	 *
	 * @return string
	 *
	 */
	public function getValue($key)
	{
		return self::$config_values[$key];
	}
}
