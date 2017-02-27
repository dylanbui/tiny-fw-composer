<?php
	// define('ENV_PRODUCTION', 'production');
	// define('ENV_STAGING', 'staging');
	// define('ENV_TEST', 'test');
	// define('ENV_DEVELOPMENT', 'development');
	// // Define application environment => 'production'; 'staging'; 'test'; 'development';
	// defined('APPLICATION_ENV') || define('APPLICATION_ENV', 
	// 	(getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : ENV_DEVELOPMENT));

namespace TinyFw\Core;

use Intervention\Image\Gd\Commands\SharpenCommand;
use TinyFw\SessionManager\Session;

class Application extends Container
{
    const DEFAULT_NAMESPACE = 'App\Controller';

    protected $appConfig;
    protected $oLoader;

    function __construct($vars = array())
    {
        parent::__construct($vars);

        $oLoader = Loader::getInstance();

        // register the namspace
        $oLoader->addNamespace('App\Controller', site_path('/app/controllers'));
        $oLoader->addNamespace('App\Lib', site_path('/app/libraries'));
        $oLoader->addNamespace('App\Model', site_path('/app/models'));
        $oLoader->addNamespace('App\Helper', site_path('/app/helpers'));

        $oLoader->register();

        $this->oLoader = $oLoader;

        // -- Define variables --
        $this->initVars();
    }


    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    protected function initVars()
    {
        // Create configure object
        $oConfig = new Config();
        // -- Load default config file --
        $oConfig->load(site_path('/app/config/config.php'));
        // -- Load routes config file --
        $oConfig->load(site_path('/app/config/routes.php'), 'routes');

        $this->set('oConfig', $oConfig);
        $this->appConfig = $oConfig->get('application');

        // -- Start Session Object --
        $sessionConfig = $oConfig->get('session');
        $this->set('oSession', function () use ($sessionConfig) {
            // SessionManager
            $oSession = new Session($sessionConfig);
            return $oSession;
        });

        // -- Start Cookie Object --
        $this->set('oCookie', function () use ($sessionConfig) {
//            // Cookie
//            $oCookie = new Cookie(
//                $sessionConfig['cookie_name'],
//                null,
//                0,
//                $sessionConfig['cookie_path'],
//                $sessionConfig['cookie_domain'],
//                $sessionConfig['cookie_secure'],
//                $sessionConfig['cookie_httponly']
//                );
//            return $oCookie;
            $oCookie = new Cookie('cookies_site');
            return $oCookie;
        });


        $this->set('oInput', function () {
            // Input
            $oInput = new Input();
            return $oInput;
        });

        $this->set('oView', function () {
            // View
            $oView = new View('default/default');
            $oView->setTemplateDir(__VIEW_PATH);
            $oView->setLayoutDir(__LAYOUT_PATH);
            return $oView;
        });

        $this->set('oResponse', function () {
            // Response
            $oResponse = new Response();
            $oResponse->addHeader('Content-Type:text/html; charset=utf-8');
            return $oResponse;
        });

        $this->set('oDispatcher', function () use ($oConfig) {
            // Dispatcher
            $oDispatcher = new Dispatcher(self::DEFAULT_NAMESPACE);
            // Add Hook Config
            $hookConfig = $oConfig->get('hooks', array());
            foreach (array('pre_controller', 'post_controller') as $hookName)
            {
                if (empty($hookConfig[$hookName]))
                    continue;
                foreach ($hookConfig[$hookName] as $item)
                {
                    $params = empty($item['params']) ? array() : $item['params'];
                    $namespace = empty($item['namespace']) ? null : $item['namespace'];
                    $request = new Request($item['path'], $params, $namespace);
                    if ($hookName == 'pre_controller')
                        $oDispatcher->addPreRequest($request);
                    elseif ($hookName == 'post_controller')
                        $oDispatcher->addPostRequest($request);
                }
            }
            // Set Routes
            $oDispatcher->setRoutes($oConfig->get('routes', array()));
            // Set default URI
            $oDispatcher->setDefaultUri($oConfig->get('application')['default_uri']);
            return $oDispatcher;
        });

        date_default_timezone_set($this->appConfig['timezone']);

        // Register exception handler
        ExceptionHandler::register();
    }

    public function run()
    {
        // -- Send dispatcher --
        $this->oDispatcher->send();

        //-- Level = 0 => get default compression level --
        if ($this->oResponse->getLevel() == 0)
            $this->oResponse->setLevel($this->appConfig['config_compression']);

        if (is_null($this->oResponse->getOutput()))
            $this->oResponse->setOutput($this->oView->getContent());

        // -- echo html content --
        $this->oResponse->output();
    }

}
