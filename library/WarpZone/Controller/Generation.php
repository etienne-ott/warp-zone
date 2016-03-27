<?php
namespace WarpZone\Controller;

use WarpZone\Entity\Section,
    WarpZone\Entity\Entry,
    WarpZone\ElementFormat;
/**
 * Handles request for the frontpage.
 */
class Generation extends \WarpZone\Controller\AbstractController
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
     * Is called when the main page should be rebuild.
     *
     * @param array $args The route parameters
     */
    public function rebuildAction($args)
    {
        if (!empty($_POST)) {
            $this->handleEntryFormData($_POST);
        }

        $generator = new \WarpZone\Generator();
        $settings  = \Slim\Slim::getInstance()->config('settings');
        $errors    = $generator->rebuildMain($settings);

        if (!empty($errors)) {
            // Eventually we will add the errors to a flash messenger and display
            // them on the redirected page, but for now there's no such thing
        }

        $this->redirect('/');
    }

    protected function handleEntryFormData($data)
    {
        if (
            isset($data['url'])
            && isset($data['displayName'])
            && (
                isset($data['section'])
                || isset($data['newSection'])
            )
        ) {
            $url     = $data['url'];
            $name    = $data['displayName'];
            $section = isset($data['section']) ? $data['section'] : ElementFormat::DEFAULT_SECTION;
            $newSec  = isset($data['newSection']) ? $data['newSection'] : null;

            if (!empty($newSec)) {
                $section = $newSec;
            }

            $found = Section::findOneByName($section);
            if (!$found instanceof Section) {
                $found = Section::create($section, 0);
            }

            $entry = Entry::create($url, $name, $found, 0);
        }
    }
}