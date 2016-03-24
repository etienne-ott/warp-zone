<?php
namespace WarpZone\Exception;

class FileNotFound extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}