<?php
namespace WarpZone\Entity;

use WarpZone\Exception\FileNotFound;
/**
 * Represents a section by which entries are grouped and provides functionality
 * to read/write and manipulate sections.
 */
class Section
{
    const CSV_SEP_CHAR = ",";

    protected $sectionId;

    protected $name;

    protected $priority;

    public function __construct($id)
    {
        $this->sectionId = $id;
    }

    public function getSectionId()
    {
        return $this->sectionId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($prio)
    {
        $this->priority = $prio;
        return $this;
    }

    public static function findOneBySectionId($id)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM section
                WHERE section_id = :id";

        $result = $db->executeQuery($sql, array('id' => $id));
        $row    = $result->fetch();

        if (!isset($row['section_id'])) {
            return null;
        }

        $section = new self((int)$row['section_id']);
        $section->setName($row['name'])
            ->setPriority((int)$row['priority']);

        return $section;
    }

    public static function findAll()
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM section";

        $result = $db->executeQuery($sql);
        $rows   = $result->fetchAll();

        if (empty($rows)) {
            return null;
        }

        $sections = array();

        foreach ($rows as $row) {
            $section = new self((int)$row['section_id']);
            $section->setName($row['name'])
                ->setPriority((int)$row['priority']);
            $sections[] = $section;
        }

        return $sections;
    }

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
        $section = new self();
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
            throw new FileNotFound("Could not find or open file: $filename");
        }

        $headers = fgetcsv($handle, 0, self::CSV_SEP_CHAR);
        $sections = array();

        while (($data = fgetcsv($handle, 0, self::CSV_SEP_CHAR)) !== FALSE) {
            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $arr[$headers[$i]] = $data[$i];
            }
            $sections[] = self::createFromArray($arr);
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
            throw new FileNotFound("Could not find or open file: $filename");
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