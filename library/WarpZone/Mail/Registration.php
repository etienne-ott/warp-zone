<?php
namespace WarpZone\Mail;

use WarpZone\Entity\UserCredentials;

class Registration extends \WarpZone\Mail\AbstractMail
{
    protected $receivers;

    public function __construct(UserCredentials $cred)
    {
        parent::__construct($cred->getUser()->getEmail());

        $settings = \Slim\Slim::getInstance()->config('settings');

        $this->subject = "Warp Zone: Please activate your account";
        $this->message = sprintf("Hello %s,\n\nYou receive this mail because "
            . "this email address was registered at %s. If you got this mail "
            . "in error, simply ignore it and your account will not be activated "
            . "and will be deleted after a certain timespan of inactivity.\n\n"
            . "In order to activate your account, simply follow this link:\n"
            . "%s\n\nHave a nice day!",
            $cred->getUser()->getName(),
            $settings->App->base_url,
            $settings->App->base_url . '/confirm/' . $cred->getOptinHash()
        );
    }
}