<?php
namespace Glossary;

class Router
{
    /**
     * Returns a list of routes that should be registered with the Slim app.
     * Each route is an array with the following structure:
     *
     * array(
     *     'pattern'    => string The route pattern to be used
     *     'template'   => string The template to be rendered for the route
     *     'controller' => string The class name of the controller that should
     *                            be called. May be not given, which is represented
     *                            by an empty string
     *     'action'     => string The action of the controller to be called before
     *                            rendering
     * )
     *
     * @return array A list of routes with the structure listed above
     */
    public function getRoutes()
    {
        return array(
            array(
                'pattern'    => '/',
                'template'   => 'index.phtml',
                'controller' => 'Glossary\Controller\Index',
                'action'     => 'index',
            ),
            array(
                'pattern'    => '/definition',
                'template'   => 'definition.phtml',
                'controller' => 'Glossary\Controller\Definition',
                'action'     => 'index',
            ),
            array(
                'pattern'    => '/definition/:term',
                'template'   => 'definition.phtml',
                'controller' => 'Glossary\Controller\Definition',
                'action'     => 'define',
            ),
            array(
                'pattern'    => '/definition/ajax/:term',
                'template'   => 'definition.phtml',
                'controller' => 'Glossary\Controller\Definition',
                'action'     => 'ajax',
            ),
        );
    }
}