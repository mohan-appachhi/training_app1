<?php
use Phalcon\Mvc\Model\Manager  as ModelsManager;
use Phalcon\Flash\Session as FlashSession;

error_reporting(E_ALL);

use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Logger\Adapter\File as FileAdapter;
try {
    define('APP_PATH', realpath('..') . '/');

    /**
     * Read the configuration
     */
    $config = new ConfigIni(APP_PATH . 'app/config/config.ini');
    if (is_readable(APP_PATH . 'app/config/config.ini.dev')) {
        $override = new ConfigIni(APP_PATH . 'app/config/config.ini.dev');
        $config->merge($override);
    }

    /**
     * Auto-loader configuration
     */
    require APP_PATH . 'app/config/loader.php';

    /**
     * Load application services
     */
    require APP_PATH . 'app/config/services.php';

    $application = new Application($di);

    echo $application->handle()->getContent();
} catch (Exception $e){
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}