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
 * @return array A list of error messages if errors occurred during execution
 */
function rebuildMain() {
    $errors = array();

    try {
        $template = new Template('templates/index.html');
        
        $entries = Entry::readFromCsvFile(ENTRIES_FILENAME);
        $entriesHtml = ElementFormat::formatEntries($entries);

        $sections = Section::readFromCsvFile(SECTIONS_FILENAME);
        $optionsHtml = ElementFormat::formatOptions($sections);

        $html = $template->replace("columns", $entriesHtml)
            ->replace("selectOptions", $optionsHtml)
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
function hasRelevantPostData($data) {
    return isset($data['submit']) && (!empty($data['url']) || !empty($data['newSection']));
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
            'displayName' => empty($data['displayName']) ? $data['url'] : $data['displayName']
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
            'name' => $name
        ));

        $sections = Section::readFromCsvFile(SECTIONS_FILENAME);
        $sections = Section::addIfNotExists($sections, $section);
        Section::writeToCsvFile(SECTIONS_FILENAME, $sections);
    } catch (Exception $e) {
        $errors[] = sprintf("Could not check/save section: %s\n", $e->getMessage());
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

if (hasRelevantPostData($_POST)) {
    $errors = array_merge($errors, checkAndAddNewSection($_POST));
    $errors = array_merge($errors, createOrUpdateEntry($_POST));
}

$errors = array_merge($errors, rebuildMain());

// Not all errors are caught by try-catch, therefore we also check for any premature
// output to check if something went wrong
if (empty($errors) && ob_get_length() === 0) {
    header('Location: ./index.html');
} else {
    ob_end_flush();
    echo implode('<br/>', $errors);
}