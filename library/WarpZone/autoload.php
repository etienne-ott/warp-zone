<?php

/**
 * Register an autoloader for WarpZone classes by their namespace. Only
 * classes within the namespace WarpZone are required, others are ignored
 * by this autoloader.
 */
spl_autoload_register(function($name) {
    if (strcasecmp(substr(ltrim($name, "\\"), 0, 8), 'WarpZone') === 0) {
        require_once (__DIR__ . '/../' . str_replace('\\', '/', $name) . '.php');
    }
});