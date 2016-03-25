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
     * @var \WarpZone\Config $parent A (possible) parent Config instance, from
     *    which this instance derives.
     */
    protected $parent;

    /**
     * @var string $parentKey If a parent is set, denotes the key under which
     *    this instance originated.
     */
    protected $parentKey;

    /**
     * Constructs a new config instance wrapped on the given settings array.
     *
     * @param array $config The settings structured like the array returned
     *    from parse_ini_file.
     */
    public function __construct($config = array(), $parent = null, $parentKey = null)
    {
        $this->config    = $config;
        $this->parent    = $parent;
        $this->parentKey = $parentKey;
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
     * Writes the settings of the Config instance to the given file in the ini file
     * format. Note that regular ini files as understood by PHP's ini file functions
     * do not support nested sections, so this method does not handle them either.
     *
     * @param string $filename The filename of the ini file to write
     * @throws \Exception when the given file could not be opened for writing
     */
    public function writeToIniFile($filename)
    {
        $fileHandle = fopen($filename, 'w');
        if (!$fileHandle) {
            throw new \Exception("Could not open given filename $filename for writing.");
        }

        foreach ($this->config as $key => $value) {
            fwrite($fileHandle, sprintf("[%s]\n", $key));
            if (is_array($value)) {
                foreach ($value as $innerKey => $innerValue) {
                    // Ini files do not support nested sections, so we don't have
                    // to check for a depper hierachy at this point.
                    fwrite($fileHandle, sprintf("%s = \"%s\"\n", $innerKey, $innerValue));
                }
            } else {
                fwrite($fileHandle, sprintf("%s = \"%s\"\n", $key, $value));
            }
            fwrite($fileHandle, "\n");
        }

        fclose($fileHandle);
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
                return new self($this->config[$name], $this, $name);
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

        if ($this->parent instanceof \WarpZone\Config) {
            $this->parent->{$this->parentKey} = $this->config;
        }
    }
}