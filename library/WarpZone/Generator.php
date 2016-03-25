<?php
namespace WarpZone;

use WarpZone\Template,
    WarpZone\Exception\FileNotFound,
    WarpZone\Entity\Entry,
    WarpZone\Entity\Section,
    WarpZone\ElementFormat,
    lessc;

class Generator
{
    /**
     * Rebuilds the index.html file and compiles all LESS style files
     * to CSS.
     *
     * @param \WarpZone\Config $settings The settings to use for rebuilding
     * @return array A list of error messages if errors occurred during execution
     */
    public function rebuildMain($settings) {
        $errors = array();

        try {
            $template = new Template('index.phtml');
            
            $sections = Section::findAll();
            $optionsHtml = ElementFormat::formatOptions($sections);

            $entries = Entry::findAll();
            $entriesHtml = ElementFormat::formatEntries($entries, $sections);

            $themes = glob(APPLICATION_PATH . '/styles/*.less');
            $themesHtml = ElementFormat::formatThemeOptions($themes, $settings);

            $html = $template->replace("columns", $entriesHtml)
                ->replace("selectOptions", $optionsHtml)
                ->replace("themeOptions", $themesHtml)
                ->replace("activeTheme", $settings->Theme->active_theme)
                ->render();
            file_put_contents(APPLICATION_PATH . "/templates/generated.phtml", $html);
        } catch (Exception $e) {
            $errors[] = sprintf("Could not generate: %s\n", $e->getMessage());
        }

        foreach (glob(APPLICATION_PATH . '/styles/*.less') as $filename) {
            $lessCompiler = new lessc();
            $cssFilename = substr($filename, 0, strlen($filename) - 4) . 'css';
            try {
                $lessCompiler->compileFile($filename, $cssFilename);
            } catch (Exception $e) {
                $errors[] = sprintf("Could not compile less file %s: %s\n", $filename, $e->getMessage());
            }
        }

        return $errors;
    }
}