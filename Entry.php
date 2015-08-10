<?php
/**
 * Represents an entry containing data for an URL (e.g. name, priority, etc.)
 * and provides functionality to read/write and manipulate entries.
 */
class Entry {
    /**
     * @var array $fields A list of fields the class uses.
     */
    public static $fields = array(
        "url",         // URL of the link
        "displayName", // A name displayed instead of the raw URL
        "section",     // The name of the section
        "priority",    // Entries are sorted by descending priority
    );

    /**
     * Constructs an entry from the given data array. Checks for the
     * existence of field names (see @self::$fields) as keys in the
     * given data array and if present, copies the values to the created
     * instance.
     *
     * @param array $arr The data array
     * @return Entry The created entry
     */
    public static function createfromArray($arr) {
        $entry = new Entry();
        foreach (self::$fields as $fieldName) {
            if (isset($arr[$fieldName])) {
                $entry->$fieldName = $arr[$fieldName];
            }
        }
        return $entry;
    }

    /**
     * Reads entries from the given filename. The file is treated as CSV file
     * and the first line is assumed to contain the headers. The entries are
     * constructed from the data in the file under the assumption that the
     * headers correspond to entry fields (see @self::$fields) in any order.
     * 
     * @param string The path and filename of the file to read
     * @throws FileNotFoundException if the file wasn't found
     * @return array A list of Entry instances
     */
    public static function readFromCsvFile($filename) {
        $handle = fopen($filename, "r");
        if ($handle === FALSE) {
            throw new FileNotFoundException("Could not find or open file: $filename");
        }

        $headers = fgetcsv($handle, 0, CSV_SEP_CHAR);
        $entries = array();

        while (($data = fgetcsv($handle, 0, CSV_SEP_CHAR)) !== FALSE) {
            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $arr[$headers[$i]] = $data[$i];
            }
            $entries[] = Entry::createFromArray($arr);
        }

        fclose($handle);
        return $entries;
    }

    /**
     * Writes the given list of entries to the given file. The file is created
     * as CSV file with the fields of the entry (see @self::$fields) as headers.
     *
     * @param string $filename The path and filename where the file will be saved
     * @param array $entries A list of entries
     * @throws FileNotFoundException if the file could not be created/opened
     */
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
                $headerMap['priority'] => $entry->priority,
            ));
        }

        fclose($handle);
    }

    /**
     * Replaces or adds the given entry in the given list of entries. Looks
     * for an entry with the exact same URL as the given one and replaces it
     * completely if found.
     *
     * @param array $entries A list of entries
     * @param Entry $newEntry The entry to add/replace
     * @return array A list of entries including the new one
     */
    public static function replaceOrAddEntryInList($entries, $newEntry) {
        $foundEntry = false;
        foreach ($entries as $index => $entry) {
            if ($entry->url === $newEntry->url) {
                $entries[$index] = $newEntry;
                $foundEntry = true;
            }
        }

        if (!$foundEntry) {
            $entries[] = $newEntry;
        }

        return $entries;
    }

    /**
     * Returns the entry as array with field=>value as key=>value pairs.
     *
     * @return array An array with the data of the entry
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