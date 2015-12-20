<?php
namespace Glossary;

class View extends \Slim\View
{
    /**
     * @var string The base URL of our application.
     */
    protected $_baseUrl = '/glossary';

    /**
     * @see \Slim\View::render()
     */
    public function render($template)
    {
        return parent::render($template);
    }

    /**
     * Formats the given route fragment so it completes the baseURL to form
     * a valid URL that points to the correct URI for what we're linking.
     * This method is designed to not touch already valid global URLs outside
     * the app space.
     *
     * @param string $route The route to link to
     * @return string The formatted URL
     */
    public function url($route)
    {
        if (stristr($route, ':') !== false) {
            // Looks like we got a global URL with procotol and whatnot
            return $route;
        }

        if (strpos($route, '//') === 0) {
            // A lazy-format global URL
            return $route;
        }

        // Now we are reasonably certain we got in fact a route relative to our
        // base URL

        // Check if route already begins with slash
        $slash = false;
        if (strpos($route, '/') === 0) {
            $slash = true;
        }

        return $this->_baseUrl . ($slash ? '' : '/') . $route;
    }
}