<?php
include_once "Exceptions.php";

class Template {
    private $html;

    public function __construct($filename) {
        if (!file_exists($filename)) {
            throw new FileNotFoundException("Could not find template file: $filename");
        }
        $this->html = file_get_contents($filename);
    }

    public function replace($id, $replace) {
        $this->html = preg_replace('#<%ph\s+id="' . $id . '"\s*%>#', $replace, $this->html);
        return $this;
    }

    public function render() {
        return $this->html;
    }
}