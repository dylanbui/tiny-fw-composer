<?php

namespace TinyFw;


use TinyFw\Core\Input;
use TinyFw\Core\Config;
use TinyFw\Core\Registry;
use TinyFw\SessionManager\Session;

class Application
{
    protected $oRegister;


    function __construct()
    {
        $this->oRegister = Registry::getInstance();

        // Config
        $this->oConfig = $this->createConfig();

        // Loader
        $this->oLoader = $this->createLoader();

        $this->oFront = $this->createFrontController();
    }

    public function __get($key)
    {
        return $this->oRegister->{$key};
    }

    public function __set($key, $value)
    {
        $this->oRegister->{$key} = $value;
    }

    protected function createConfig()
    {
        // Create configure object
        $oConfig = new Config();

        // -- Load default config file --
        if(file_exists(site_path('/app/config/config.php')))
            $oConfig->load(site_path('/app/config/config.php'));

        return $oConfig;
    }

    protected function createLoader()
    {
        $oLoader = Core\Loader::getInstance();

        // Register the namspace
        $oLoader->addNamespace('App\Controller', site_path('/app/controllers'));
        $oLoader->addNamespace('App\Lib', site_path('/app/libraries'));
        $oLoader->addNamespace('App\Model', site_path('/app/models'));
        $oLoader->addNamespace('App\Helper', site_path('/app/helpers'));

        $oLoader->register();

        return $oLoader;
    }

    protected function createSession()
    {
        // SessionManager
        $params = $this->oConfig->config_values['session'];
        $oSession = new SessionManager\Session($params);
        return $oSession;
    }

    protected function createInput()
    {
        // Input
        $oInput = new Input();
        return $oInput;
    }

    protected function createView()
    {
        // View
        $oView = new Core\View('default/default');
        $oView->setTemplateDir(site_path('/app/views'));
        $oView->setLayoutDir(site_path('/app/layouts'));
        return $oView;
    }

    protected function createResponse()
    {
        // Response
        $oResponse = new Core\Response();
        $oResponse->addHeader('Content-Type:text/html; charset=utf-8');
        return $oResponse;
    }

    protected function createFrontController()
    {
        // Initialize the FrontController
        $oFront = Core\FrontController::getInstance();
        $oFront->setDefaultControllerNamespace('App\Controller'); // Default : 'App\Controller'
        return $oFront;
    }


    public function run()
    {
        date_default_timezone_set($this->oConfig->config_values['application']['timezone']);

        // register exception handler
        Core\ExceptionHandler::register();

        $this->oSession = $this->createSession();

        $this->oInput = $this->createInput();

        $this->oView = $this->createView();

        $this->oResponse = $this->createResponse();

        // Start
        $this->oFront->dispatch();

        //-- Level = 0 => get default compression level --
        if ($this->oResponse->getLevel() == 0)
            $this->oResponse->setLevel($this->oConfig->config_values['application']['config_compression']);

        if (is_null($this->oResponse->getOutput()))
            $this->oResponse->setOutput($this->oView->getContent());

        //-- TODO : Hook before output content html --
//        $this->oResponse->setOutput(
//            $this->oView->getContent(),
//            $this->oConfig->config_values['application']['config_compression']);

        // -- echo html content --
        $this->oResponse->output();
    }
}
