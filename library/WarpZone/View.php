<?php
namespace WarpZone;

use WarpZone\ElementFormat;

class View extends \Slim\View
{
    /**
     * @var string The base URL of our application.
     */
    protected $_baseUrl = null;

    protected $formData = array();

    protected $errors = array();

    /**
     * @see \Slim\View::render()
     */
    public function render($template, $data = null)
    {
        return parent::render($template, $data);
    }

    /**
     * Returns the base URL used for constructing relative URI paths. Lazy-loads
     * the base URL from the app config if it is not set.
     *
     * @return string The base URL
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $config = \Slim\Slim::getInstance()->config('settings');
            $this->_baseUrl = $config->App->base_url;
        }
        return $this->_baseUrl;
    }

    /**
     * Sets the base URL to the given value.
     *
     * @param string $url The base URL
     * @return $this
     */
    public function setBaseUrl($url)
    {
        $this->_baseUrl = $url;
        return $this;
    }

    public function getFormData()
    {
        return $this->formData;
    }

    public function setFormData($data)
    {
        $this->formData = $data;
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Formats the given route fragment so it completes the baseURL to form
     * a valid URL that points to the correct URI for what we're linking.
     * This method is designed to not touch already valid global URLs outside
     * the app space.
     *
     * @param string $route The route to link to
     * @return string The formatted URL
     */
    public function url($route)
    {
        if (stristr($route, ':') !== false) {
            // Looks like we got a global URL with procotol and whatnot
            return $route;
        }

        if (strpos($route, '//') === 0) {
            // A lazy-format global URL
            return $route;
        }

        // Now we are reasonably certain we got in fact a route relative to our
        // base URL

        // Check if route already begins with slash
        $slash = false;
        if (strpos($route, '/') === 0) {
            $slash = true;
        }

        return $this->getBaseUrl() . ($slash ? '' : '/') . $route;
    }

    /**
     * Returns the javascript code that should be included in the view content
     * the script files are bound externally. This is useful and necessary
     * because these scripts might require installation or request specific data,
     * such as the base URL of the installation or user data.
     *
     * @return string The javascript code to be inline before the script files
     */
    public function getInlineJavascript()
    {
        // Various settings
        $js = sprintf("var settings = function() {
            this.system = {}
            this.system.baseUrl = '%s'
        }", $this->url('/'));
    }

    /**
     * Tries to extract the form data for the field with the given
     * name from the view. If not found, returns an empty string.
     *
     * @param string $name The name of the form field
     * @return string The form data for the given name or an empty string
     */
    public function formField($name)
    {
        return isset($this->formData[$name]) ? $this->formData[$name] : '';
    }

    /**
     * Tries to extract the form error for the field with the given name
     * from the view. If not found, returns an empty string.
     * If the formatted flag was set, automatically formats the errors. If
     * no error was found, returns an empty string instead of a formatted
     * empty error. This is useful so the method can always be called,
     * regardless of whether there was an error or not.
     *
     * @param string The name of the form field
     * @param boolean $doFormat (Optional) If true, formats the error.
     *    Defaults to true.
     * @return string A (possibly empty) string with the error. May be
     *    formatted.
     */
    public function formError($name, $doFormat = true)
    {
        return isset($this->errors[$name])
            ? ElementFormat::formatError($this->errors[$name])
            : '';
    }
}