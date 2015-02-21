<?php
// Turn on all errors and buffer any output. This is later used for
// checking against any errors.
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

include_once "Template.php";
include_once "Exceptions.php";
include_once "Entry.php";
include_once "Section.php";
include_once "ElementFormat.php";
include_once "library/less/lessc.inc.php";

define("DEFAULT_COLUMN", 'Read later');
define("CSV_SEP_CHAR", ',');
define("ENTRIES_FILENAME", 'entries.csv');
define("SECTIONS_FILENAME", 'sections.csv');

/**
 * Rebuilds the index.html file and compiles all LESS style files
 * to CSS.
 *
 * @param array A map of settings with the same structure as the config file
 * @return array A list of error messages if errors occurred during execution
 */
function rebuildMain($settings) {
    $errors = array();

    try {
        $template = new Template('templates/index.html');
        
        $sections = Section::readFromCsvFile(SECTIONS_FILENAME);
        $optionsHtml = ElementFormat::formatOptions($sections);

        $entries = Entry::readFromCsvFile(ENTRIES_FILENAME);
        $entriesHtml = ElementFormat::formatEntries($entries, $sections);

        $themes = glob('styles/*.less');
        $themesHtml = ElementFormat::formatThemeOptions($themes, $settings);

        $html = $template->replace("columns", $entriesHtml)
            ->replace("selectOptions", $optionsHtml)
            ->replace("themeOptions", $themesHtml)
            ->render();
        file_put_contents("index.html", $html);
    } catch (Exception $e) {
        $errors[] = sprintf("Could not build index: %s\n", $e->getMessage());
    }

    $lessCompiler = new lessc();
    foreach (glob('styles/*.less') as $filename) {
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
function hasRelevantContentData($data) {
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
function hasRelevantThemeData($data) {
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
function createOrUpdateEntry($data) {
    $errors = array();

    try {
        $entry = Entry::createFromArray(array(
            'url' => $data['url'],
            'section' => empty($data['newSection']) ? $data['section'] : $data['newSection'],
            'displayName' => empty($data['displayName']) ? $data['url'] : $data['displayName'],
            'weight' => 0
        ));

        $entries = Entry::readFromCsvFile(ENTRIES_FILENAME);
        $entries = Entry::replaceOrAddEntryInList($entries, $entry);
        Entry::writeToCsvFile(ENTRIES_FILENAME, $entries);
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
function checkAndAddNewSection($data) {
    $errors = array();

    try {
        $name = empty($data['newSection']) ? $data['section'] : $data['newSection'];
        $section = Section::createFromArray(array(
            'name' => $name,
            'weight' => 0
        ));

        $sections = Section::readFromCsvFile(SECTIONS_FILENAME);
        $sections = Section::addIfNotExists($sections, $section);
        Section::writeToCsvFile(SECTIONS_FILENAME, $sections);
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
function updateThemeSettings($data, &$settings) {
    $errors = array();

    try {
        $iniContent = file_get_contents('config.ini');
        $iniContent = preg_replace(
            '#active_theme\s*=.*#',
            'active_theme = "' . $data['theme'] . '"',
            $iniContent
        );
        file_put_contents('config.ini', $iniContent);
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

// Now, do the thing

$errors = array();

if (!file_exists(ENTRIES_FILENAME)) {
    file_put_contents(ENTRIES_FILENAME, implode(CSV_SEP_CHAR, Entry::$fields) . PHP_EOL);
}

if (!file_exists(SECTIONS_FILENAME)) {
    file_put_contents(SECTIONS_FILENAME, implode(CSV_SEP_CHAR, Section::$fields) . PHP_EOL);
}

if (!file_exists('config.ini')) {
    copy('config-default.ini', 'config.ini');
}

$settings = parse_ini_file('config.ini', true);

if (hasRelevantContentData($_POST)) {
    $errors = array_merge($errors, checkAndAddNewSection($_POST));
    $errors = array_merge($errors, createOrUpdateEntry($_POST));
}

if (hasRelevantThemeData($_POST)) {
    $errors = array_merge($errors, updateThemeSettings($_POST, $settings));
}

$errors = array_merge($errors, rebuildMain($settings));

// Not all errors are caught by try-catch, therefore we also check for any premature
// output to check if something went wrong
if (empty($errors) && ob_get_length() === 0) {
    header('Location: ./index.html');
} else {
    ob_end_flush();
    echo implode('<br/>', $errors);
}