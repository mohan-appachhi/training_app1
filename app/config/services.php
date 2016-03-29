<?php 

use \Phalcon\Mvc\View;
use \Phalcon\DI\FactoryDefault;
use \Phalcon\Dispathcer;
use \Phalcon\Url as UrlProvider;
use \Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use \Phaclon\Mvc\Model\Metadata\Memory as MetaData;
use \Phalcon\Session\Adapter\Files as SessionAdapter;
use \Phalcon\Flash\Session as FlashSession;
use Phalcon\Logger\Adapter\File as FileAdapter;
use \Phalcon\Event\Manager as EventManager;
try
{
	$di = new FactoryDefault();

	/*************************************************************************/
	/* View Service                                                          */
	/*************************************************************************/
	
	$di->set('view',function () use ($config)
	{
		$view = new View();

		$view->setViewsDir(APP_PATH . $config->application->viewsDir);
		$view->registerEngines([
			'.volt' => 'volt'
			]);
		return $view;
	});
	/*************************************************************************/
	/*  Volt Service                                                         */
	/*************************************************************************/
	$di->set('volt', function ($view, $di) {

		$volt = new VoltEngine($view, $di);

		$volt->setOptions(array(
			"compiledPath" => APP_PATH . "cache/volt/"
		));

		$compiler = $volt->getCompiler();
		$compiler->addFunction('is_a', 'is_a');

		return $volt;
	}, true);

	$di->set('modelsMetadata', function () {
		return new MetaData();
	});
	/*************************************************************************/
	/*  BAse Url Service                                                     */
	/*************************************************************************/
	$di->set('url', function()
	{
	    $url = new Phalcon\Mvc\Url();
	    $url->setBaseUri('/training_app1/');
	    return $url;
	});

	/*************************************************************************/
	/*  Session Service                                                      */
	/*************************************************************************/
	$di->set('session', function () {
		$session = new SessionAdapter();
		$session->start();
		return $session;
	});
	/*************************************************************************/
	/*  Falsh Service                                                        */
	/*************************************************************************/
	$di->set('flash', function () {
		return new FlashSession(array(
			'error'   => 'alert alert-danger',
			'success' => 'alert alert-success',
			'notice'  => 'alert alert-info',
			'warning' => 'alert alert-warning'
		));
	});

	/*************************************************************************/
	/*  Routing Service                                                      */
	/*************************************************************************/
	$di->set('router', function () {
		return require ('router.php');
	});
	/*************************************************************************/
	/*  Logger Service                                                       */
	/*************************************************************************/
	$di->setShared('logger', function() {
    return new FileAdapter(__DIR__.'/../var/logs/nix/APP-'.date('Y-m-d').'.log', array(
        'mode' => 'a+'));
	});

	$di->set('utils', function () {
		return new utils();
	});
	/*************************************************************************/
	/*  Type Validator                                                       */
	/*************************************************************************/
	$di->setShared('validator', function() {
		return new Validator();
	});

} 
catch (Exception $e) {
	echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}