<?php
namespace WarpZone\Entity;

use WarpZone\Exception\FileNotFound,
    WarpZone\Entity\Section;
/**
 * Represents an entry containing data for an URL (e.g. name, priority, etc.)
 * and provides functionality to read/write and manipulate entries.
 */
class Entry
{
    protected $entryId;

    protected $url;

    protected $displayName;

    protected $section;

    protected $priority;

    public function __construct($id)
    {
        $this->entryId = $id;
    }

    public function getEntryId()
    {
        return $this->entryId;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setDisplayName($name)
    {
        $this->displayName = $name;
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setSection(Section $section)
    {
        $this->section = $section;
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

    public static function findOneByEntryId($id)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM entry
                WHERE entry_id = :id";

        $result = $db->executeQuery($sql, array('id' => $id));
        $row    = $result->fetch();

        if (!isset($row['entry_id'])) {
            return null;
        }

        if (isset($row['section_id']) && !empty($row['section_id'])) {
            $section = Section::findOneByEntryId($row['section_id']);
        } else {
            $section = null;
        }

        $entry = new self((int)$row['entry_id']);
        $entry->setUrl($row['url'])
            ->setDisplayName($row['display_name'])
            ->setSection($section)
            ->setPriority((int)$row['priority']);

        return $entry;
    }

    public static function findAll()
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM entry";

        $result = $db->executeQuery($sql);
        $rows   = $result->fetchAll();

        if (empty($rows)) {
            return null;
        }

        $sections = array();
        $entries  = array();

        foreach ($rows as $row) {
            if (isset($row['section_id']) && !empty($row['section_id'])) {
                if (isset($sections[$row['section_id']])) {
                    $section = $sections[$row['section_id']];
                } else {
                    $section = Section::findOneBySectionId($row['section_id']);
                    $sections[$row['section_id']] = $section;
                }
            } else {
                $section = null;
            }

            $entry = new self((int)$row['entry_id']);
            $entry->setUrl($row['url'])
                ->setDisplayName($row['display_name'])
                ->setSection($section)
                ->setPriority((int)$row['priority']);
            $entries[] = $entry;
        }

        return $entries;
    }

    public static function create($url, $name, Section $section, $priority = 0)
    {
        if (empty($url) || empty($name)) {
            throw new \Exception("Empty URL or name for entry provided in Entry::create.");
        }

        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "INSERT INTO entry (url, display_name, section_id, priority)
                VALUES (:url, :name, :secId, :prio)";

        $db->executeQuery($sql, array(
            'url'   => $url,
            'name'  => $name,
            'secId' => $section->getSectionId(),
            'prio'  => (int)$priority,
        ));
        $id = $db->lastInsertId();

        $entry = new self($id);
        $entry->setUrl($url)
            ->setDisplayName($name)
            ->setSection($section)
            ->setPriority((int)$priority);

        return $entry;
    }
}