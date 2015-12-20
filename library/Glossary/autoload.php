<?php

/**
 * Register an autoloader for glossary classed by their namespace.
 *
 * @todo Use a generated classmap here; also support multiple/internal classes
 *    in one file
 */
spl_autoload_register(function($name) {
    require_once (__DIR__ . '/../' . str_replace('\\', '/', $name) . '.php');
});