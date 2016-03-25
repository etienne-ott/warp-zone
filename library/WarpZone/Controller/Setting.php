<?php
namespace WarpZone\Controller;

/**
 * Handles request for the frontpage.
 */
class Setting extends \WarpZone\Controller\AbstractController
{
    /**
     * Is called when the theme for the main page should be changed.
     *
     * @param array $args The route parameters
     */
    public function changeThemeAction($args)
    {
        $name = isset($_POST['theme']) ? $_POST['theme'] : '';
        if (file_exists(APPLICATION_PATH . '/styles/' . $name . '.less')) {
            $config = \Slim\Slim::getInstance()->config('settings');
            $config->Theme->active_theme = $name;
            $config->writeToIniFile(APPLICATION_PATH . '/config.ini');
            \Slim\Slim::getInstance()->config('settings', $config);
        } else {
            // Eventually we will print an error message via the flash messenger
            // but there is no such thing yet
        }

        $this->redirect('/rebuild');
    }
}