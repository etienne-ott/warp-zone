<?php
namespace WarpZone\Entity;

use WarpZone\Exception\FileNotFound;
/**
 * Represents a section by which entries are grouped and provides functionality
 * to read/write and manipulate sections.
 */
class Section
{
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
}