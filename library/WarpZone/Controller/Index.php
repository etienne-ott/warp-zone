<?php
namespace WarpZone\Controller;

/**
 * Handles request for the frontpage.
 */
class Index extends \WarpZone\Controller\AbstractController
{
    /**
     * @see \WarpZone\Controller\AbstractController::__construct
     */
    public function __construct($view)
    {
        $session = \Slim\Slim::getInstance()->config('session');
        if (!$session->getUser() instanceof \WarpZone\Entity\User) {
            $this->redirect('/login');
        }

        parent::__construct($view);
    }
    /**
     * Is called when the main page should be display.
     *
     * @param array $args The route parameters
     */
    public function indexAction($args)
    {

    }
}