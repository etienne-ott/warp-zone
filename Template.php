<?php
include_once "Exceptions.php";

/**
 * A class to wrap around text content containing placeholders that can be
 * identified and replaced by a given ID.
 *
 * Warning: This class offers limited functionality for now and might be
 * expanded in the future.
 *
 * Placeholders are represented in the text content as self-closing <%-tags of
 * type "ph" with parameter "id".
 * Example: <%ph id="author" %> can be identified by the ID "author" and be
 * replaces with any text content.
 *
 * Other parameters are not supported at the moment and will likely stop the
 * templating from working correctly.
 */
class Template {
    /**
     * @var The current text content of the template
     */
    private $html;

    /**
     * Constructs a new instance from the given file.
     * 
     * @param string $filename The path and name of the file to load.
     */
    public function __construct($filename) {
        if (!file_exists($filename)) {
            throw new FileNotFoundException("Could not find template file: $filename");
        }
        $this->html = file_get_contents($filename);
    }

    /**
     * Replaces all placeholders with the given ID in the current text content
     * of the template. Once the placeholder is replaced, it cannot be targeted
     * again.
     *
     * @param string $id The ID by which to identify the placeholders
     * @param string $replace The replacement text content
     * @return Template The template as fluid interface
     */
    public function replace($id, $replace) {
        $this->html = preg_replace('#<%ph\s+id="' . $id . '"\s*%>#', $replace, $this->html);
        return $this;
    }

    /**
     * Returns the rendered template as text content.
     * Warning: For now this simply returns the current text content including
     * placeholders that have not been replaced yet.
     *
     * @return string The rendered template
     */
    public function render() {
        return $this->html;
    }
}