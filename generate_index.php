<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

include_once "Template.php";
include_once "Exceptions.php";
include_once "Entry.php";
include_once "ElementFormat.php";
include_once "library/less/lessc.inc.php";

define("DEFAULT_COLUMN", 'Read later');
define("CSV_FILENAME", 'entries.csv');

function rebuildMain() {
    $errors = array();

    try {
        $template = new Template('templates/index.html');
        
        $entries = Entry::readFromCsvFile(CSV_FILENAME);
        $entriesHtml = ElementFormat::formatEntries($entries);
        $optionsHtml = ElementFormat::formatOptions($entries);

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

function hasRelevantPostData($data) {
    return isset($data['submit']) && (!empty($data['url']) || !empty($data['newSection']));
}

function createOrUpdateEntry($data) {
    $errors = array();

    try {
        $entry = Entry::createFromArray(array(
            'url' => $data['url'],
            'section' => empty($data['section']) ? DEFAULT_COLUMN : $data['section'],
            'displayName' => empty($data['displayName']) ? $data['url'] : $data['displayName']
        ));

        $entries = Entry::readFromCsvFile(CSV_FILENAME);
        $entries = Entry::replaceOrAddEntryInList($entries, $entry);
        Entry::writeToCsvFile(CSV_FILENAME, $entries);
    } catch (Exception $e) {
        $errors[] = sprintf("Could not update/save entry: %s\n", $e->getMessage());
    }

    return $errors;
}

function checkAndAddNewSection($data) {
    $errors = array();
    return $errors;
}

// Do the thing
$errors = array();

if (!file_exists(CSV_FILENAME)) {
    file_put_contents(CSV_FILENAME, "url,displayName,section,weight\n");
}

if (hasRelevantPostData($_POST)) {
    $errors = array_merge($errors, checkAndAddNewSection($_POST));
    $errors = array_merge($errors, createOrUpdateEntry($_POST));
}

$errors = array_merge($errors, rebuildMain());

if (empty($errors) && ob_get_length() === 0) {
    header('Location: ./index.html');
} else {
    ob_end_flush();
    echo implode('<br/>', $errors);
}