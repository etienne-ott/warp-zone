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
        }

        $headers = fgetcsv($handle, 0, ",");
        $entries = array();

        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $arr[$headers[$i]] = $data[$i];
            }
            $entries[] = Entry::createFromArray($arr);
        }

        fclose($handle);
        return $entries;
    }

    public static function writeToCsvFile($filename, $entries) {
        $handle = fopen($filename, "w");
        if ($handle === FALSE) {
            throw new FileNotFoundException("Could not find or open file: $filename");
        }

        fputcsv($handle, self::$fields);
        $headerMap = array_flip(self::$fields);

        foreach ($entries as $entry) {
            fputcsv($handle, array(
                $headerMap['url'] => $entry->url,
                $headerMap['displayName'] => $entry->displayName,
                $headerMap['section'] => $entry->section,
                $headerMap['weight'] => 0 // Not implemented yet
            ));
        }

        fclose($handle);
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