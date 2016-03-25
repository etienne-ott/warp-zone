<?php
namespace WarpZone;

/**
 * Provides functionality to render/format given elements as HTML structures.
 * For example a list of Entry instances could be rendered as HTML lists with
 * each entry as a list item.
 */
class ElementFormat
{
    const DEFAULT_COLUMN = 'Read later';

    /**
     * Returns a function used in sorting functions to sort objects by their
     * priority. Assumes the objects have a getter named getPriority().
     *
     * @return function A function used in sorting function such as uasort()
     */
    public static function getPrioritySortFunction() {
        return function($a, $b) {
            return $b->getPriority() - $a->getPriority();
        };
    }

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
                . '<div class="entryListTitle">' . htmlentities(utf8_encode($name))
                . '</div>' . PHP_EOL . '<ul class="entryList">' . PHP_EOL;
        };

        uasort($sections, self::getPrioritySortFunction());

        foreach ($sections as $section) {
            $tables[$section->getName()] = $sectionHeader($section->getName());
        }

        uasort($entries, self::getPrioritySortFunction());

        foreach($entries as $entry) {
            $sectionName = $entry->getSection()->getName();
            if (!isset($tables[$sectionName])) {
                $tables[$sectionName] = $sectionHeader($sectionName);
            }

            $tables[$sectionName] .= '<li><a href="' . $entry->getUrl() . '">' . htmlentities(utf8_encode($entry->getDisplayName()));
            $tables[$sectionName] .= '</a></li>' . PHP_EOL;
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
        uasort($sections, self::getPrioritySortFunction());

        $html = '<option class="selectOption" value="' . self::DEFAULT_COLUMN . '">- Default section -</option>' . PHP_EOL;
        foreach ($sections as $section) {
            $html .= '<option class="selectOption" value="' . utf8_encode($section->getName()) . '">'
                . htmlentities(utf8_encode($section->getName())) . '</option>' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Formats each theme filename as <option> for the theme select field
     * with the name of the theme both as value and label. This method
     * automatically removes anything that looks like a path or extension.
     * 
     * @param array $themes A list of theme filenames
     * @param \WarpZone\Config The settings to use for formatting
     * @return string The formatted HTML structure
     */
    public static function formatThemeOptions($themes, $settings) {
        $html = "";

        foreach ($themes as $filename) {
            $name  = str_replace('\\', '/', $filename);
            $parts = explode('/', $name);
            $name  = end($parts);
            $parts = explode('.', $name);
            $name  = reset($parts);
            $html .= '<option class="selectOption" '
                . ($settings->Theme->active_theme == $name ? 'selected="true" ' : '')
                . 'value="' . $name . '">' . $name . '</option>' . PHP_EOL;
        }

        return $html;
    }
}