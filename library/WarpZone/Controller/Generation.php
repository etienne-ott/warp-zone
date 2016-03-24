<?php
namespace WarpZone\Controller;

/**
 * Handles request for the frontpage.
 */
class Generation extends \WarpZone\Controller\AbstractController
{
    /**
     * Is called when the main page should be rebuild.
     *
     * @param array $args The route parameters
     */
    public function rebuildAction($args)
    {
        $generator = new \WarpZone\Generator();
        $settings  = \Slim\Slim::getInstance()->config('settings');
        $errors    = $generator->rebuildMain($settings);

        if (!empty($errors)) {
            // Eventually we will add the errors to a flash messenger and display
            // them on the redirected page, but for now there's no such thing
        }

        $this->redirect('/');
    }
}