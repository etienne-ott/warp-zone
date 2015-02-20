<?php
/**
 * An exception thrown when a required file could not be found
 * or opened.
 */
class FileNotFoundException extends Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}