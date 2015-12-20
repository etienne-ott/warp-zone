<?php
namespace Glossary\Controller;

/**
 * Handles request about definitions.
 */
class Definition extends \Glossary\Controller\AbstractController
{
    /**
     * Handles when a term search form has been fired. Reads the search term
     * from the form then either redirects to the definition page or back
     * to the front page.
     *
     * @param array $args The route parameters
     */
    public function indexAction($args)
    {
        if (
            isset($_POST['searchTerm'])
            && !empty($_POST['searchTerm'])
        ) {
            $this->redirect('definition/' . urlencode($_POST['searchTerm']));
        } else {
            $this->redirect('/');
        }
    }

    /**
     * Is called when a definition should be displayed. Further definitions
     * will be loaded on demand, so we start with only the central one.
     *
     * @param array $args The route parameters
     */
    public function defineAction($args)
    {
        $term = $this->cleanTerm(urldecode(reset($args)));
        $filename = APPLICATION_PATH . '/data/' . $term . '.json';
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename));
            $this->_view->mainTerm = $data->term;
            $this->_view->mainDescription = $this->formatDescription($data->description);
        } else {
            $this->_view->errors = array('Suchbegriff nicht gefunden.');
        }
    }

    public function ajaxAction($args)
    {
        $term = $this->cleanTerm(urldecode(reset($args)));
        $filename = APPLICATION_PATH . '/data/' . strtolower($term) . '.json';
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename));
            echo "<div class=\"definitionCard main\">
                    <span class=\"definitionTerm\">"
                         . $data->term
                    . "</span>

                    <br/>

                    <span class=\"definitionDescription\">"
                        . $data->description
                    . "</span>
                </div>";
        }
        exit;
    }

    /**
     * Cleans the given seach term for its use in the filename based system.
     *
     * @param string $term The search term
     * @return string The cleaned search term
     */
    public function cleanTerm($term)
    {
        $search  = array('\r', '\n', '\t', ' ');
        $replace = array('', '', '', '-');
        $str     = strtolower(str_replace($search, $replace, $term));
        $str     = preg_replace('[^\w-]', '', $term);
        return $str;
    }

    /**
     * Formats the given description for its use in the output.
     * This also looks for other key words in the description and highlights
     * them for the user, so they can choose to load the definition.
     *
     * @todo Extract search for keywords into creation of cards or regular
     *    check. This is too costly to do on every request.
     * @todo The key word search does not work properly yet. This probably
     *    needs an external library for matching words in text
     * @param string $desc The description
     * @param string The formatted description
     */
    public function formatDescription($desc)
    {
        $search  = array('\r', '\n', '\t');
        $replace = array('', '<br/>', '');
        $str = str_replace($search, $replace, $desc);

        $matches = array();
        $pattern = '#\s(\w+)[\s\.,!\?"\']#m';
        preg_match_all($pattern, $str, $matches);

        if (isset($matches[1])) {
            foreach ($matches[1] as $match) {
                $trimmed = trim($match);
                $term = $this->cleanTerm($trimmed);

                $filename = APPLICATION_PATH . '/data/' . strtolower($term) . '.json';
                if (file_exists($filename)) {
                    $str = str_replace($trimmed, '<span class="termLink" data-term="' . $trimmed . '">' . $trimmed . '</span>', $str);
                }
            }
        }

        return $str;
    }
}