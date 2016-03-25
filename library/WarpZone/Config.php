<?php
namespace WarpZone;

class Config
{
    /**
     * @var array $config A nested array containing the settings, structured
     *    like the array returned from parse_ini_file.
     */
    protected $config;

    /**
     * Constructs a new config instance wrapped on the given settings array.
     *
     * @param array $config The settings structured like the array returned
     *    from parse_ini_file.
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * Constructs a new config instance from the settings saved in the given ini
     * file.
     *
     * @param string $filename The ini file to read from
     * @throws \WarpZone\Exception\FileNotFound if the given file was not found
     * @return \WarpZone\Config The newly created Config instance
     */
    public static function fromIniFile($filename)
    {
        if (file_exists($filename)) {
            $settings = parse_ini_file($filename, true);
        } else {
            throw new \WarpZone\Exception\FileNotFound("Could not find ini file $filename.");
        }
        return new self($settings);
    }

    /**
     * Returns the value of the setting with the given name. If it a nested
     * hierarchy, and the value therefore contains more key<->value pairs,
     * returns the sub-config as instance of \WarpZone\Config, to enable
     * sucessive calls of -> on the settings tree.
     *
     * @param string $name The name of the setting.
     * @return mixed The value of the setting as scalar, \WarpZone\Config or
     *    null, if the setting was not found
     */
    public function __get($name)
    {
        if (isset($this->config[$name])) {
            if (is_array($this->config[$name])) {
                return new self($this->config[$name]);
            } else {
                return $this->config[$name];
            }
        } else {
            return null;
        }
    }

    /**
     * Sets the setting with the given name to the given value.
     *
     * @param string $name The name of the setting
     * @param mixed $value The value of the setting
     * @throws \Exception if the given value was neither a scalar, array or
     *    instance of \WarpZone\Config
     */
    public function __set($name, $value)
    {
        if ($value instanceof \WarpZone\Config) {
            $value = $value->config;
        }

        if (is_object($value)) {
            throw new \Exception(sprintf(
                "Unsupported type for given setting value. Expected scalar, "
                . "array or \\WarpZone\\Config, got %s instead.",
                get_class($value)
            ));
        }

        $this->config[$name] = $value;
    }
}