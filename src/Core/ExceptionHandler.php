<?php
/*
 * https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Error
*/

namespace TinyFw\Core;

/**
 * Class Error
 * @package Phalcon\Error
 *
 * @method int type()
 * @method string message()
 * @method string file()
 * @method string line()
 * @method \Exception exception()
 * @method bool isException()
 * @method bool isError()
 */
class Error
{
    /**
     * @var array
     */
    protected $attributes;
    /**
     * Class constructor sets the attributes.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'type'        => -1,
            'message'     => 'No error message',
            'file'        => '',
            'line'        => '',
            'exception'   => null,
            'isException' => false,
            'isError'     => false,
        ];
        $options = array_merge($defaults, $options);
        foreach ($options as $option => $value) {
            $this->attributes[$option] = $value;
        }
    }
    /**
     * Magic method to retrieve the attributes.
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return isset($this->attributes[$method]) ? $this->attributes[$method] : null;
    }
}


class ExceptionHandler
{
    /**
     * @var string
     */
    protected static $templateErrorDir;

    /**
     * Registers itself as error and exception handler.
     * @param : string
     * @return void
     */
    public static function register($templateErrorDir = null)
    {
        self::$templateErrorDir = $templateErrorDir;

        if (!defined('APPLICATION_ENV'))
            define('APPLICATION_ENV', 'development');

        // Define application environment => 'production'; 'staging'; 'test'; 'development';
        switch (APPLICATION_ENV) {
            case 'production':
            case 'staging':
            default :
                ini_set('display_errors', 0);
                error_reporting(0);
                break;
            case 'test':
            case 'development':
                ini_set('display_errors', 1);
                // error_reporting(-1);
//                error_reporting(E_ALL ^ E_DEPRECATED); // Hien thi thong bao tat ca cac loi tru cac ham DEPRECATED
                error_reporting(E_ALL); // Hien thi tat ca thong bao loi
                break;
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (!($errno & error_reporting())) {
                return;
            }
            $options = [
                'type'    => $errno,
                'message' => $errstr,
                'file'    => $errfile,
                'line'    => $errline,
                'isError' => true,
            ];
            static::handle(new Error($options));
        });

        // -- From PHP 7.0 using \Throwable instead \Exception -- (\Throwable $e) {}
        set_exception_handler(function ($e) {
            $options = [
                'type'        => $e->getCode(),
                'message'     => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'isException' => true,
                'exception'   => $e,
            ];
            static::handle(new Error($options));
        });

        register_shutdown_function(function () {
            if (!is_null($options = error_get_last())) {
                static::handle(new Error($options));
            }
        });
    }

    /**
     * Logs the error and dispatches an error controller.
     *
     * @param  Error $error
     * @return mixed
     */
    public static function handle(Error $error)
    {
        $errorDir = realpath(dirname(__FILE__)).'/template_errors';
        $view_file = $errorDir.'/error_php.phtml';
        if($error->isException())
        {
            if(!is_null(self::$templateErrorDir))
                $errorDir = self::$templateErrorDir;

            if(APPLICATION_ENV == 'production' ||
                APPLICATION_ENV == 'staging' || $error->type() == 404)
                $view_file = $errorDir.'/error_404.phtml';

            if($error->type() == 500)
                $view_file = $errorDir.'/error_500.phtml'; // -- Permission Deny --
        }

        if(!file_exists($view_file))
            die('Not found : ' . $view_file);

        // -- TODO: Luc chay duoc luc khong --
        @ob_end_clean();
        $view = new View();
        echo $view->parser($view_file, array('error'=>$error));
    }
}