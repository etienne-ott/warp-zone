<?php
/**
 * Provides functionality to render/format given elements as HTML structures.
 * For example a list of Entry instances could be rendered as HTML lists with
 * each entry as a list item.
 */
class ElementFormat {
    /**
     * Formats the given list of entries as <ul> lists with the entries as list
     * items. For each different section a new wrapper is created wrapping
     * around the list itself and a <span> section label.
     * 
     * @param array A list of entries to format
     * @return string The formatted HTML structure
     */
    public static function formatEntries($entries) {
        $tables = array();

        uasort($entries, function($a, $b) {
            return (isset($a->weight) ? $a->weight : 0)
                - (isset($b->weight) ? $b->weight : 0);
        });

        foreach($entries as $entry) {
            if (!isset($tables[$entry->section])) {
                $tables[$entry->section] = '<div class="entryListWrapper">' . PHP_EOL
                    . '<span class="entryListTitle">' . $entry->section . '</span>' . PHP_EOL
                    . '<ul class="entryList">' . PHP_EOL;
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
     * Extracts the sections from the given list of entries and formats each
     * section as <option> for a select input field with the name of the
     * section both as value and label.
     * 
     * @param array A list of entries to format
     * @return string The formatted HTML structure
     */
    public static function formatOptions($entries) {
        $sections = array();
        foreach ($entries as $entry) {
            if (!isset($sections[$entry->section])) {
                $sections[$entry->section] = $entry->section;
            }
        }

        $html = '<option class="selectOption" value="' . DEFAULT_COLUMN . '"></option>' . PHP_EOL;
        foreach ($sections as $section) {
            $html .= '<option class="selectOption" value="' . $section . '">' . $section . '</option>' . PHP_EOL;
        }

        return $html;
    }
}