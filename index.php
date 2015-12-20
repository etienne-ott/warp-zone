<?php

// This is final, we do not negotiate about error reporting.
// We show all errors and that's it. No excuses.
error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Useful globals
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/'));

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Register autoloaders
require 'library/vendor/autoload.php';
require 'library/Glossary/autoload.php';

// Bootstrap the Slim app
$app = new \Slim\Slim(array(
    'templates.path' => './templates',
    'view'           => new \Glossary\View(),
));

// Add the routes to the app
$router = new \Glossary\Router();
foreach ($router->getRoutes() as $route) {
    $app->map($route['pattern'], function() use ($app, $route) {
        // Check if the given controller needs to do something
        if (!empty($route['controller'])) {
            $action     = isset($route['action']) ? $route['action'] : 'index';
            $controller = new $route['controller']($app->view());
            $controller->action($action, func_get_args());
        }

        // Render the template
        $app->render($route['template']);
    })->via('GET', 'POST');
}

// All done, run
$app->run();