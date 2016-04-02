<?php
namespace WarpZone\Exception;

class InvalidMail extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}