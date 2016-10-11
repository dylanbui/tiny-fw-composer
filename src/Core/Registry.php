<?php

namespace TinyFw\Core;

class Registry {

    /*
     * @var object $instance
    */
    private static $instance = null;

    /**
     *
     * Return Registry instance or create intitial instance
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
            self::$instance = new self(); //Registry();
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

    /*
    * @the vars array
    * @access private
    */
    private $vars = array();

    /**
     *
     * @set undefined vars
     *
     * @param string $index
     *
     * @param mixed $value
     *
     * @return void
     *
     */
    public function __set($index, $value)
    {
        self::$instance->vars[$index] = $value;
    }

    /**
     *
     * @get variables
     *
     * @param mixed $index
     *
     * @return mixed
     *
     */
    public function __get($index)
    {
        return (isset(self::$instance->vars[$index]) ? self::$instance->vars[$index] : NULL);
    }

    public function has($index)
    {
        return isset(self::$instance->vars[$index]);
    }
}

?>
