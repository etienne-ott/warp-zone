<?php
namespace WarpZone\Mail;

use WarpZone\Exception\InvalidMail;

class AbstractMail
{
    protected $receivers;

    protected $subject = "Mail subject";

    protected $message = "Mail message";

    public function __construct($receivers)
    {
        if (!empty($receivers)) {
            $this->receivers = is_array($receivers) ? $receivers : array($receivers);
        } else {
            $this->receivers = array();
        }
    }

    public function send()
    {
        if (empty($this->receivers)) {
            throw new InvalidMail("Tried to send mail with no receivers.");
        }

        $sender  = \Slim\Slim::getInstance()->config('settings')->App->mail_sender;
        $to      = implode(',', $this->receivers);
        $headers = 'From: ' . $sender . "\r\n"
            . 'Reply-To: ' . $sender;

        mail($to, $this->subject, $this->message, $headers);
    }
}