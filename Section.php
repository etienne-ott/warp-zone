<?php

class Section {
    private static $fields = array(
        "name",  // Name of the section
        "weight" // Sections are sorted by ascending weight
    );

    public static function createfromArray($arr) {
        $section = new Section();
        foreach (self::$fields as $fieldName) {
            if (isset($arr[$fieldName])) {
                $section->$fieldName = $arr[$fieldName];
            }
        }
        return $section;
    }

    public static function readFromCsvFile($filename) {
        $handle = fopen($filename, "r");
        if ($handle === FALSE) {
            throw new FileNotFoundException("Could not find or open file: $filename");
        }

        $headers = fgetcsv($handle, 0, ",");
        $sections = array();

        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $arr[$headers[$i]] = $data[$i];
            }
            $sections[] = Section::createFromArray($arr);
        }

        fclose($handle);
        return $sections;
    }

    public static function addIfNotExists($sections, $newSection) {
        $foundSection = false;
        foreach ($sections as $section) {
            if ($section->name === $newSection->name) {
                $foundSection = true;
            }
        }

        if (!$foundSection) {
            $sections[] = $newSection;
        }

        return $sections;
    }

    public static function writeToCsvFile($filename, $sections) {
        $handle = fopen($filename, "w");
        if ($handle === FALSE) {
            throw new FileNotFoundException("Could not find or open file: $filename");
        }

        fputcsv($handle, self::$fields);
        $headerMap = array_flip(self::$fields);

        foreach ($sections as $section) {
            fputcsv($handle, array(
                $headerMap['name'] => $section->name,
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