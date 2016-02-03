<?php

// This is final, we do not negotiate about error reporting.
// We show all errors and that's it. No excuses.
error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Useful globals
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

require __DIR__ . '/../library/vendor/autoload.php';
require __DIR__ . '/../library/WarpZone/autoload.php';

// Read config file
if (file_exists(APPLICATION_PATH . '/config.ini')) {
    $config = parse_ini_file(APPLICATION_PATH . '/config.ini', true);
} else {
    $config = parse_ini_file(APPLICATION_PATH . '/config-default.ini', true);
}

// Create database connection
$dbConfig           = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'dbname'   => $config['Test-DB']['name'],
    'user'     => $config['Test-DB']['user'],
    'password' => $config['Test-DB']['password'],
    'host'     => $config['Test-DB']['host'],
    'driver'   => 'pdo_mysql',
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $dbConfig);

// Bootstrap the Slim app
$app = new \Slim\Slim(array(
    'templates.path' => './templates',
    'view'           => new \WarpZone\View(),
    'settings'       => $config,
    'db'             => $conn,
));