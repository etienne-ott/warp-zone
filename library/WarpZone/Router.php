<?php
namespace WarpZone;

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
                'template'   => 'generated.phtml',
                'controller' => 'WarpZone\Controller\Index',
                'action'     => 'index',
            ),
            array(
                'pattern'    => '/rebuild',
                'template'   => '',
                'controller' => 'WarpZone\Controller\Generation',
                'action'     => 'rebuild',
            ),
        );
    }

    /**
     * Callback method when a matching route was found and should
     * now be rendered. The given route should be of the same structure as
     * the return values of WarpZone\Router::getRoutes().
     *
     * @param array $route The matching route as associative array as in
     *    WarpZone\Router::getRoutes()
     * @param array $params The route parameters structured as in func_get_args()
     */
    public function routeCallback(array $route, array $params)
    {
        $app = \Slim\Slim::getInstance();

        // Check if the given controller needs to do something
        if (!empty($route['controller'])) {
            $action     = isset($route['action']) ? $route['action'] : 'index';
            $controller = new $route['controller']($app->view());
            $controller->action($action, $params);
        }

        // Render the template
        $app->render($route['template']);
    }
}