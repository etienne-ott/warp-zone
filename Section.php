<?php
/**
 * Represents a section by which entries are grouped and provides functionality
 * to read/write and manipulate sections.
 */
class Section {
    /**
     * @var array $fields A list of fields the class uses.
     */
    public static $fields = array(
        "name",  // Name of the section
        "priority" // Sections are sorted by descending priority
    );

    /**
     * Constructs a section from the given data array. Checks for the
     * existence of field names (see @self::$fields) as keys in the
     * given data array and if present, copies the values to the created
     * instance.
     *
     * @param array $arr The data array
     * @return Section The created section
     */
    public static function createfromArray($arr) {
        $section = new Section();
        foreach (self::$fields as $fieldName) {
            if (isset($arr[$fieldName])) {
                $section->$fieldName = $arr[$fieldName];
            }
        }
        return $section;
    }

    /**
     * Reads sections from the given filename. The file is treated as CSV file
     * and the first line is assumed to contain the headers. The sections are
     * constructed from the data in the file under the assumption that the
     * headers correspond to section fields (see @self::$fields) in any order.
     * 
     * @param string The path and filename of the file to read
     * @throws FileNotFoundException if the file wasn't found
     * @return array A list of Section instances
     */
    public static function readFromCsvFile($filename) {
        $handle = fopen($filename, "r");
        if ($handle === FALSE) {
            throw new FileNotFoundException("Could not find or open file: $filename");
        }

        $headers = fgetcsv($handle, 0, CSV_SEP_CHAR);
        $sections = array();

        while (($data = fgetcsv($handle, 0, CSV_SEP_CHAR)) !== FALSE) {
            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $arr[$headers[$i]] = $data[$i];
            }
            $sections[] = Section::createFromArray($arr);
        }

        fclose($handle);
        return $sections;
    }

    /**
     * Adds the given section to the given list of sections, if it does not
     * already exist, identified by exact name.
     *
     * @param array $sections A list of sections
     * @param Section $newSection A section to insert/update
     */
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

    /**
     * Writes the given list of sections to the given file. The file is created
     * as CSV file with the fields of the section (see @self::$fields) as headers.
     *
     * @param string $filename The path and filename where the file will be saved
     * @param array $sections A list of sections
     * @throws FileNotFoundException if the file could not be created/opened
     */
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
                $headerMap['priority'] => $section->priority
            ));
        }

        fclose($handle);
    }

    /**
     * Returns the section as array with field=>value as key=>value pairs.
     *
     * @return array An array with the data of the section
     */
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