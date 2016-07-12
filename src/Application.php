<?php
	// define('ENV_PRODUCTION', 'production');
	// define('ENV_STAGING', 'staging');
	// define('ENV_TEST', 'test');
	// define('ENV_DEVELOPMENT', 'development');
	// // Define application environment => 'production'; 'staging'; 'test'; 'development';
	// defined('APPLICATION_ENV') || define('APPLICATION_ENV', 
	// 	(getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : ENV_DEVELOPMENT));

namespace TinyFw;

//	// define the site path __SITE_PATH : c:\xampp\htdocs\adv_mvc
//	define ('__SITE_PATH', realpath(dirname(dirname(__FILE__))));
//	// __SITE_URL : /adv_mvc/
//    $tmp = str_replace('public_html/', '', $_SERVER['SCRIPT_NAME']);
//    define ('__SITE_URL', str_replace(basename($tmp), '', $tmp));
//// 	define ('__SITE_URL', str_replace('/'.basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']));
//
//	// __BASE_URL : /adv_mvc/
//// 	define ('__BASE_URL', __SITE_URL);
// 	// Co thu muc public_html
// 	define ('__PUBLIC_HTML', __SITE_URL);
//
// 	// ---- Khong Thay Doi ---- //
// 	define ('__ASSET_URL', __PUBLIC_HTML.'assets');
//    define ('__COMPONENT_URL', __ASSET_URL.'/plugins');
// 	define ('__IMAGE_URL', __ASSET_URL.'/images');
// 	define ('__CSS_URL', __ASSET_URL.'/css');
// 	define ('__JS_URL', __ASSET_URL.'/js');
//
//	// the application directory path
//	define ('__APP_PATH', __SITE_PATH.'/app');
//	define ('__VIEW_PATH', __APP_PATH.'/views');
//	define ('__LAYOUT_PATH', __APP_PATH.'/layouts');
//	// define ('__HELPER_PATH', __APP_PATH.'/helpers');
////	define ('__CONFIG_PATH', __APP_PATH.'/config');
//
//	define ('__UPLOAD_DATA_PATH', __SITE_PATH.'/public_html/data/upload/');
//	define ('__UPLOAD_DATA_URL', __PUBLIC_HTML . 'data/upload/');
//
//	define ('__DATA_PATH', __SITE_PATH . '/public_html/data/');
//	define ('__DATA_URL', __PUBLIC_HTML . 'data/');

//	define ('__CONTROLLER_NAMESPACE', 'App\Controller');

 //    // Load config files. Global config file
	// require __SITE_PATH.'/app/libraries/Core/Psr4Autoloader.php';
	// // instantiate the loader
 // 	$loader = new Psr4Autoloader();
 // 	// register the base directories for the namespace prefix
 	
 // 	$loader->addNamespace(__CONTROLLER_NAMESPACE, __APP_PATH.'/controllers'); 	
 // 	$loader->addNamespace('App\Lib', __SITE_PATH.'/app/libraries'); 	
 // 	$loader->addNamespace('App\Model', __SITE_PATH.'/app/models');
 // 	$loader->addNamespace('App\Helper', __SITE_PATH.'/app/helpers');
 // 	// register the autoloader
 // 	$loader->register();

//    echo "<pre>";
//    print_r($_SERVER);
//    echo "</pre>";
//
//    $const = get_defined_constants(true);
//    echo "<pre>";
//    print_r($const['user']);
//    echo "</pre>";
//    exit();

 //    // Create configure object
 //    $config = \App\Lib\Core\Config::getInstance();

	// define('APPLICATION_ENV', $config->config_values['application']['application_env']); 
	// // set the timezone
	// date_default_timezone_set($config->config_values['application']['timezone']);	

 //    require __SITE_PATH . '/app/config/constants.php';   

	// /*** set error handler level to E_WARNING ***/
	// // error_reporting($config->config_values['application']['error_reporting']);
	// // set_error_handler('_exception_handler', $config->config_values['application']['error_reporting']);
	// \App\Lib\Core\ErrorHandler::register();

//	$registry = null;
//	$config = null;
//
//	require __SITE_PATH . '/admin/startup.php';
//
//	$oBenchmark = new \App\Lib\Core\Benchmark();
//	$oBenchmark->mark('code_start');
//
// 	/*** a new registry object ***/
// 	$registry = new \App\Lib\Core\Registry();
//
//	// Loader
//	$registry->oLoader = $loader;
//
// 	// Session
// 	$oSession = new \App\Lib\Session();
// 	$registry->oSession = $oSession;
//
// 	// Input
// 	$oInput = new \App\Lib\Input();
// 	$registry->oInput = $oInput;
//
//	// Response
//	$response = new \App\Lib\Core\Response();
//	$response->addHeader('Content-Type: text/html; charset=utf-8');
//	$registry->oResponse = $response;
//
//	// Config
//	$registry->oConfig = $config;
//
//	// Parameter
//	$view = new \App\Lib\Core\View();
//    $view->setTemplateDir(__VIEW_PATH);
//    $view->setLayoutDir(__LAYOUT_PATH);
//	$registry->oView = $view;
//
//	// Initialize the FrontController
//	$front = \App\Lib\Core\FrontController::getInstance();
//	$front->setRegistry($registry);
	
	/*
		// Cau hinh cho cac action nay chay dau tien 
	$front->addPreRequest(new Request('run/first/action')); 
	$front->addPreRequest(new Request('run/second/action'));
	*/

//    $front->addPreRequest(new \App\Lib\Core\Request('member-manager/member/get-login-info'));
//
//	$front->dispatch();
//
//	// Output
//	$response->output();
//
//	if($config->config_values['application']['show_benchmark'])
//	{
//		$oBenchmark->mark('code_end');
//		echo "<br>".$oBenchmark->elapsed_time('code_start', 'code_end');
//	}



class Application
{
    protected $oRegister;
//    protected $oConfig;
//    protected $oLoader;
//    protected $oFront;

    function __construct()
    {
        // Load constants file
//        require_once __SITE_PATH.'/vendor/autoload.php';
//        require __SITE_PATH.'/app/config/constants.php';

//        require __SITE_PATH.'/app/libraries/Core/Autoloader.php';
//        $this->loader = require __SITE_PATH.'/app/libraries/Core/Autoloader.php';

//        $this->loader = Loader::getInstance();
//
//        // register the namspace
//        $this->loader->addNamespace('App\Controller', __SITE_PATH.'/app/controllers');
//        $this->loader->addNamespace('App\Lib', __SITE_PATH.'/app/libraries');
//        $this->loader->addNamespace('App\Model', __SITE_PATH.'/app/models');
//        $this->loader->addNamespace('App\Helper', __SITE_PATH.'/app/helpers');
//        $this->loader->addPsr4("App\Controller\\", __SITE_PATH.'/app/controllers');
//        $this->loader->addPsr4("App\Lib\\", __SITE_PATH.'/app/libraries');
//        $this->loader->addPsr4("App\Model\\", __SITE_PATH.'/app/models');
//        $this->loader->addPsr4("App\Helper\\", __SITE_PATH.'/app/helpers');

        // register the autoloader
//        $this->loader->register();

        $this->oRegister = new Core\Registry();
        // Loader
//        $this->registry->oLoader = $this->loader;

//        $this->createApplication();

        $this->oRegister->oConfig = $this->createConfig();

        $this->oRegister->oLoader = $this->createLoader();

        $this->oRegister->oFront = $this->createFrontController();
    }

    public function __get($key)
    {
        return $this->oRegister->{$key};
    }

    public function __set($key, $value)
    {
        $this->oRegister->{$key} = $value;
    }

    protected function createApplication()
    {
        $oConfig = Config::getInstance();

        // -- Load default config file --
        if(file_exists(site_path('/app/config/config.php')))
            $oConfig->load(site_path('/app/config/config.php'));

        $this->oRegister->oConfig = $oConfig;


        $oLoader = Loader::getInstance();

        // register the namspace
        $oLoader->addNamespace('App\Controller', site_path('/app/controllers'));
        $oLoader->addNamespace('App\Lib', site_path('/app/libraries'));
        $oLoader->addNamespace('App\Model', site_path('/app/models'));
        $oLoader->addNamespace('App\Helper', site_path('/app/helpers'));

        $oLoader->register();

        $this->oRegister->oLoader = $oLoader;

        $this->oRegister->oSession = new Session();

        $this->oRegister->oInput = new Input();

        $oView = new View('default/default');
        $oView->setTemplateDir(site_path('/views'));
        $oView->setLayoutDir(site_path('/layouts'));
        $this->oRegister->oView = $oView;

        $oResponse = new Response();
        $oResponse->addHeader('Content-Type:text/html; charset=utf-8');
        $this->oRegister->oResponse = $oResponse;

        // Initialize the FrontController
        $oFront = FrontController::getInstance();
        $oFront->setRegistry($this->oRegister);
        $oFront->setDefaultControllerNamespace('App\Controller'); // Default : 'App\Controller'

        $this->oRegister->oFront = $oFront;
    }

    protected function createConfig()
    {
        // Create configure object
        $oConfig = Core\Config::getInstance();

        // -- Load default config file --
        if(file_exists(site_path('/app/config/config.php')))
            $oConfig->load(site_path('/app/config/config.php'));

        return $oConfig;
    }

    protected function createLoader()
    {
        $oLoader = Core\Loader::getInstance();

        // register the namspace
        $oLoader->addNamespace('App\Controller', site_path('/app/controllers'));
        $oLoader->addNamespace('App\Lib', site_path('/app/libraries'));
        $oLoader->addNamespace('App\Model', site_path('/app/models'));
        $oLoader->addNamespace('App\Helper', site_path('/app/helpers'));

        $oLoader->register();

        return $oLoader;
    }

    protected function createSession()
    {
        // Session
        $oSession = new Session();
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
        $oFront->setRegistry($this->oRegister);
        $oFront->setDefaultControllerNamespace('App\Controller'); // Default : 'App\Controller'
        return $oFront;
    }


    public function run()
    {
        date_default_timezone_set($this->oConfig->config_values['application']['timezone']);

        // register exception handler
        Core\ExceptionHandler::register();

        $this->oRegister->oSession = $this->createSession();

        $this->oRegister->oInput = $this->createInput();

        $this->oRegister->oView = $this->createView();

        $this->oRegister->oResponse = $this->createResponse();

        // Initialize the FrontController
//        $oFront = Core\FrontController::getInstance();
//        $oFront->setRegistry($this->oRegister);
//        $oFront->setDefaultControllerNamespace('App\Controller'); // Default : 'App\Controller'
//
//        $this->oRegister->oFront = $oFront;

//        $this->loadRegister();
//
//        $this->loadConfig();
//
//        $this->loadSession();
//
//        $this->loadInput();
//
//        $this->loadView();
//
//        $this->loadCache();
//
//        $this->loadResponse();
//
//        // Initialize the FrontController
//        $this->front = \TinyFw\Core\FrontController::getInstance();
//        $this->front->setRegistry($this->registry);
//        $this->front->setDefaultControllerNamespace('App\Controller'); // Default : 'App\Controller'

        /*
            // Cau hinh cho cac action nay chay dau tien
        $front->addPreRequest(new Request('run/first/action'));
        $front->addPreRequest(new Request('run/second/action'));
        */

//        $this->oFront->addPreRequest(new \TinyFw\Core\Request('member-manager/member/get-login-info'));

//        echo "<pre>";
//        print_r(site_path('app'));
//        echo "</pre>";
//        echo "<pre>";
//        print_r(site_path('views'));
//        echo "</pre>";
//        echo "<pre>";
//        print_r(site_path('layout'));
//        echo "</pre>";
//        exit();
//
//        tinyfw_url();

        return $this->oFront;
//        $this->oFront->dispatch();

//        $p = \TinyFw\Core\Config::getInstance();
//
//        $in = new \TinyFw\Input();
//
//        echo "<pre>";
//        print_r($in->get('aaaaa','gia tri mac dinh'));
//        echo "</pre>";
//
//        echo "<pre>";
//        print_r(tinyfw_now_to_mysql());
//        echo "</pre>";
//
//        exit();

        // -- Chi de tam --
//        if($this->registry->oConfig->config_values['application']['show_benchmark'])
//        {
//            $oBenchmark->mark('code_end');
//            echo "<br>".$oBenchmark->elapsed_time('code_start', 'code_end');
//        }

    }
}
