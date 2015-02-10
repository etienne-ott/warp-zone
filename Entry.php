<?php

class Entry {
    private static $fields = array(
        "url",         // URL of the link
        "displayName", // A name displayed instead of the raw URL
        "section",     // The name of the section
        "weight"       // Entries are sorted by ascending weight
    );

    public static function createfromArray($arr) {
        $entry = new Entry();
        foreach (self::$fields as $fieldName) {
            if (isset($arr[$fieldName])) {
                $entry->$fieldName = $arr[$fieldName];
            }
        }
        return $entry;
    }

    public static function readFromCsvFile($filename) {
        $handle = fopen($filename, "r");
        if ($handle === FALSE) {
            throw new FileNotFoundException("Could not find or open file: $filename");
        } else {
            $headers = fgetcsv($handle, 0, ",");
            $entries = array();

            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $arr = array();
                for ($i = 0; $i < count($data); $i++) {
                    $arr[$headers[$i]] = $data[$i];
                }
                $entries[] = Entry::createFromArray($arr);
            }

            return $entries;
        }
    }

    public function writeToCsvFile($filename) {
        $handle = fopen($filename, "a+");
        if ($handle === FALSE) {
            throw new FileNotFoundException("Could not find or open file: $filename");
        } else {
            $headerMap = array_flip(fgetcsv($handle, 0, ","));

            fputcsv($handle, array(
                $headerMap['url'] => $this->url,
                $headerMap['displayName'] => $this->displayName,
                $headerMap['section'] => $this->section,
                $headerMap['weight'] => 0 // Not implemented yet
            ));
        }
    }

    public function toArray() {
        $arr = array();
        foreach (self::$fields as $fieldName) {
            if (isset($arr[$fieldName])) {
                $arr[$fieldName] = $entry->$fieldName;
            }
        }
        return $arr;
    }
}