<?php
namespace WarpZone;

use WarpZone\Template,
    WarpZone\Exception\FileNotFound,
    WarpZone\Entity\Entry,
    WarpZone\Entity\Section,
    WarpZone\ElementFormat,
    lessc;

class Generator
{
    const CSV_SEP_CHAR = ',';
    const ENTRIES_FILENAME = 'entries.csv';
    const SECTIONS_FILENAME = 'sections.csv';

    /**
     * Rebuilds the index.html file and compiles all LESS style files
     * to CSS.
     *
     * @param \WarpZone\Config $settings The settings to use for rebuilding
     * @return array A list of error messages if errors occurred during execution
     */
    public function rebuildMain($settings) {
        $errors = array();

        try {
            $template = new Template('index.phtml');
            
            $sections = Section::findAll();
            $optionsHtml = ElementFormat::formatOptions($sections);

            $entries = Entry::findAll();
            $entriesHtml = ElementFormat::formatEntries($entries, $sections);

            $themes = glob(APPLICATION_PATH . '/styles/*.less');
            $themesHtml = ElementFormat::formatThemeOptions($themes, $settings);

            $html = $template->replace("columns", $entriesHtml)
                ->replace("selectOptions", $optionsHtml)
                ->replace("themeOptions", $themesHtml)
                ->replace("activeTheme", $settings->Theme->active_theme)
                ->render();
            file_put_contents(APPLICATION_PATH . "/templates/generated.phtml", $html);
        } catch (Exception $e) {
            $errors[] = sprintf("Could not generate: %s\n", $e->getMessage());
        }

        foreach (glob(APPLICATION_PATH . '/styles/*.less') as $filename) {
            $lessCompiler = new lessc();
            $cssFilename = substr($filename, 0, strlen($filename) - 4) . 'css';
            try {
                $lessCompiler->compileFile($filename, $cssFilename);
            } catch (Exception $e) {
                $errors[] = sprintf("Could not compile less file %s: %s\n", $filename, $e->getMessage());
            }
        }

        return $errors;
    }

    /**
     * Returns if the given array contains POST data relevant for creating or
     * updating entries or sections. It is assumed that the given array has the
     * same structure as the global $_POST.
     *
     * @param array $data The POST data
     * @return boolean True, if there is relevant POST data, false otherwise
     */
    public function hasRelevantContentData($data) {
        return isset($data['submit']) && (!empty($data['url']) || !empty($data['newSection']));
    }

    /**
     * Returns if the given array contains POST data relevant for the theme
     * selection. It is assumed that the given array has the same
     * structure as the global $_POST.
     *
     * @param array $data The POST data
     * @return boolean True, if there is relevant POST data, false otherwise
     */
    public function hasRelevantThemeData($data) {
        return !empty($data['theme']);
    }

    /**
     * Creates or updates an entry, identified by URL, for the given POST data. It
     * is assumed that the data has the same structure as the global $_POST. The
     * entry is constructed from the given data, then saved to the entries file,
     * overwriting an existing entry if it has the exact same URL.
     *
     * @param array $data The POST data
     * @return array A list of error messages if any errors occured during execution
     */
    public function createOrUpdateEntry($data) {
        $errors = array();

        try {
            $entry = Entry::createFromArray(array(
                'url' => $data['url'],
                'section' => empty($data['newSection']) ? $data['section'] : $data['newSection'],
                'displayName' => empty($data['displayName']) ? $data['url'] : $data['displayName'],
                'priority' => 0,
            ));

            $entries = Entry::readFromCsvFile(self::ENTRIES_FILENAME);
            $entries = Entry::replaceOrAddEntryInList($entries, $entry);
            Entry::writeToCsvFile(self::ENTRIES_FILENAME, $entries);
        } catch (Exception $e) {
            $errors[] = sprintf("Could not update/save entry: %s\n", $e->getMessage());
        }

        return $errors;
    }

    /**
     * Creates or updates a section, identified by name, for the given POST data. It
     * is assumed that the data has the same structure as the global $_POST. The
     * section is constructed from the given data, then saved to the sections file,
     * overwriting an existing section if it has the exact same name.
     *
     * @param array $data The POST data
     * @return array A list of error messages if any errors occured during execution
     */
    public function checkAndAddNewSection($data) {
        $errors = array();

        try {
            $name = empty($data['newSection']) ? $data['section'] : $data['newSection'];
            $section = Section::createFromArray(array(
                'name' => $name,
                'priority' => 0,
            ));

            $sections = Section::readFromCsvFile(self::SECTIONS_FILENAME);
            $sections = Section::addIfNotExists($sections, $section);
            Section::writeToCsvFile(self::SECTIONS_FILENAME, $sections);
        } catch (Exception $e) {
            $errors[] = sprintf("Could not check/save section: %s\n", $e->getMessage());
        }

        return $errors;
    }

    /**
     * Update the theme settings for the given POST data and settings array.
     * Saves the selected theme in the config file and changes the theme
     * in the given settings array.
     *
     * @param array $data The POST data
     * @param array &$settings The settings array to update
     * @return array A list of error messages if errors occured during execution
     */
    public function updateThemeSettings($data, &$settings) {
        $errors = array();

        try {
            $iniContent = file_get_contents(APPLICATION_PATH . '/config.ini');
            $iniContent = preg_replace(
                '#active_theme\s*=.*#',
                'active_theme = "' . $data['theme'] . '"',
                $iniContent
            );
            file_put_contents(APPLICATION_PATH . '/config.ini', $iniContent);
        } catch (Exception $e) {
            $errors[] = sprintf("Could not update ini file: %s\n", $e->getMessage());
        }

        if (isset($settings['Theme']['active_theme'])) {
            $settings['Theme']['active_theme'] = $data['theme'];
        } else {
            $errors[] = sprintf("Did not find setting active_theme in section theme to update.\n");
        }

        return $errors;
    }

    public function generate()
    {
        // We buffer any output during generation to work with PHP's stupid
        // errors-are-not-exceptions policy
        ob_start();

        $errors = array();

        $path = APPLICATION_PATH . '/' . self::ENTRIES_FILENAME;
        if (!file_exists($path)) {
            file_put_contents(
                $path,
                implode(self::CSV_SEP_CHAR, Entry::$fields) . PHP_EOL
            );
        }

        $path = APPLICATION_PATH . '/' . self::SECTIONS_FILENAME;
        if (!file_exists($path)) {
            file_put_contents(
                $path,
                implode(self::CSV_SEP_CHAR, Section::$fields) . PHP_EOL
            );
        }

        if (!file_exists(APPLICATION_PATH . '/config.ini')) {
            copy(
                APPLICATION_PATH . '/config-default.ini',
                APPLICATION_PATH . '/config.ini'
            );
        }

        $settings = parse_ini_file(APPLICATION_PATH . '/config.ini', true);

        if ($this->hasRelevantContentData($_POST)) {
            $errors = array_merge($errors, $this->checkAndAddNewSection($_POST));
            $errors = array_merge($errors, $this->createOrUpdateEntry($_POST));
        }

        if ($this->hasRelevantThemeData($_POST)) {
            $errors = array_merge($errors, $this->updateThemeSettings($_POST, $settings));
        }

        $errors = array_merge($errors, $this->rebuildMain($settings));

        // Not all errors are caught by try-catch, therefore we also check for any premature
        // output to check if something went wrong
        if (ob_get_length() !== 0) {
            $errors[] = "Detected premature output during generation: " . htmlspecialchars(ob_get_contents());
        }

        ob_end_flush();
        return $errors;
    }
}