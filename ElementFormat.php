<?php
class ElementFormat {
    public static function formatEntries($entries) {
        $tables = array();

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