<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    return isset($data['submit']) && !empty($data['url']);
}

function createOrUpdateEntry($data) {
    $errors = array();

    try {
        $entry = Entry::createFromArray(array(
            'url' => $data['url'],
            'section' => empty($data['section']) ? DEFAULT_COLUMN : $data['section'],
            'displayName' => empty($data['displayName']) ? $data['url'] : $data['displayName']
        ));
        $entry->writeToCsvFile(CSV_FILENAME);
    } catch (Exception $e) {
        $errors[] = sprintf("Could not update/save entry: %s\n", $e->getMessage());
    }

    return $errors;
}

// Do the thing

if (!file_exists(CSV_FILENAME)) {
    file_put_contents(CSV_FILENAME, "url,displayName,section,weight\n");
}

if (hasRelevantPostData($_POST)) {
    $errors = createOrUpdateEntry($_POST);
}

$errors = array_merge($errors, rebuildMain());

if (empty($errors)) {
    header('Location: ./index.html');
} else {
    echo implode('<br/>', $errors);
}