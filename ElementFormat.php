<?php
/**
 * Provides functionality to render/format given elements as HTML structures.
 * For example a list of Entry instances could be rendered as HTML lists with
 * each entry as a list item.
 */
class ElementFormat {
    /**
     * Formats the given lists of entries and sections as <ul> lists with the
     * entries as list items. For each different section a new wrapper is
     * created wrapping around the list itself and a <span> section label. If
     * an entry has a section for which no corresponding section exists in
     * the section list, a new section is appended to the existing ones.
     * 
     * @param array A list of entries to format
     * @param array A list of sections
     * @return string The formatted HTML structure
     */
    public static function formatEntries($entries, $sections) {
        $tables = array();
        $sectionHeader = function($name) {
            return '<div class="entryListWrapper">' . PHP_EOL
                . '<span class="entryListTitle">' . $name . '</span>' . PHP_EOL
                . '<ul class="entryList">' . PHP_EOL;
        };

        uasort($sections, function($a, $b) {
            return (isset($a->weight) ? $a->weight : 0)
                - (isset($b->weight) ? $b->weight : 0);
        });

        foreach ($sections as $section) {
            $tables[$section->name] = $sectionHeader($section->name);
        }

        uasort($entries, function($a, $b) {
            return (isset($a->weight) ? $a->weight : 0)
                - (isset($b->weight) ? $b->weight : 0);
        });

        foreach($entries as $entry) {
            if (!isset($tables[$entry->section])) {
                $tables[$entry->section] = $sectionHeader($entry->section);
            }

            $tables[$entry->section] .= '<li><a href="' . $entry->url . '">' . $entry->displayName;
            $tables[$entry->section] .= '</a></li>' . PHP_EOL;
        }

        foreach ($tables as &$table) {
            $table .= '</ul></div>' . PHP_EOL;
        }

        return implode(PHP_EOL, $tables);
    }

    /**
     * Formats each section as <option> for a select input field with the
     * name of the section both as value and label.
     * 
     * @param array A list of sections to format
     * @return string The formatted HTML structure
     */
    public static function formatOptions($sections) {
        uasort($sections, function($a, $b) {
            return (isset($a->weight) ? $a->weight : 0)
                - (isset($b->weight) ? $b->weight : 0);
        });

        $html = '<option class="selectOption" value="' . DEFAULT_COLUMN . '"></option>' . PHP_EOL;
        foreach ($sections as $section) {
            $html .= '<option class="selectOption" value="' . $section->name . '">' . $section->name . '</option>' . PHP_EOL;
        }

        return $html;
    }
}