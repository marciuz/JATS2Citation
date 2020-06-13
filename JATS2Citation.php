<?php 

/**
 * Extract metadata from XML JATS and return an HTML string ready for citation.
 * 
 * Example:
 * 
 * $file = 'https://journals.plos.org/plosone/article/file?id=10.1371/journal.pone.0055937&type=manuscript';
 * $J2C = new JATS2Citation(); 
 * $res = $J2C->make_citation($file);
 * print $res;
 * 
 * @todo: a Formatter class for the output can be useful.
 * 
 */
class JATS2Citation {

    /**
     * For future implementation of different output formats.
     */
    const SCHEMA_HTML_DEFAULT = 'default';

    /**
     * The maximum number of authors allowed. 
     * More authors will be cited as "et al".
     * Set 0 for no limits.
     * 
     * @var int
     */
    private $max_authors = 5;

    /**
     * Set the maximum number of authors in citation.
     * Set 0 for no limits.
     *
     * @param int $n
     */
    public function set_max_authors($n) {
        $this->max_authors = (int) $n;
    }

    /**
     * Get the maximum number of allowed authors.
     *
     * @return int
     */
    public function get_max_authors() {
        return $this->max_authors;
    }

    /**
     * Make the citation.
     *
     * @param string $fpath The XML path. Can be local or remote file (url)
     * @param string $formatter For further development.
     * @todo An implementation of formatter can be useful.
     *
     * @return string
     */
    public function make_citation($fpath, $formatter=self::SCHEMA_HTML_DEFAULT) {

        $doc = new DomDocument();
        $doc->load($fpath);
        $x = new DomXPath($doc);

        $xp['title']   = $x->query('/article/front/article-meta/title-group/article-title/text()')[0]->textContent;
        $xp['journal'] = $x->query('/article/front/journal-meta/journal-title-group/journal-title/text()')[0]->nodeValue;
        $xp['volume']  = $x->query('/article/front/article-meta/volume/text()')[0]->nodeValue;
        $xp['issue']   = $x->query('/article/front/article-meta/issue/text()')[0]->nodeValue ?? null;
        $xp['eloc']    = $x->query('/article/front/article-meta/elocation-id/text()')[0]->nodeValue;
        $xp['doi']     = $x->query('/article/front/article-meta/article-id[@pub-id-type="doi"]/text()')[0]->nodeValue;
        $xp['pub_year']= $x->query('//pub-date[@pub-type="epub"]/year/text()')[0]->nodeValue;

        $_xp['surname'] = $x->query('/article/front/article-meta/contrib-group/contrib/name/surname/text()');
        $_xp['gname'] =   $x->query('/article/front/article-meta/contrib-group/contrib/name/given-names/text()');

        $xp['authors'] = $this->_make_authors_string($_xp['surname'], $_xp['gname']);

        $format_string = $this->formatter($formatter, $xp);

        return $format_string;

    }

    /**
     * For futher development.
     *
     * @param string $schema
     * @param array $xp
     * @return string
     */
    private function formatter($schema, array $xp) {

        switch($schema) {

            case self::SCHEMA_HTML_DEFAULT : 
            default : 
                return $this->_formatter_standard($xp);
        }
    }

    /**
     * Format the citation as HTML, following the PLoS rules.
     *
     * @param array $xp
     * @return string
     */
    private function _formatter_standard(array $xp) {

        $url_doi = '<a href="https://doi.org/' . $xp['doi'].'" class="cit-url" rel="nofollow">'.'https://doi.org/' . $xp['doi'].'</a>';

        $citation = $xp['authors'] . ' (' . $xp['pub_year'] .') '
            . $xp['title'] . '. ' 
            . $xp['journal'] .' '
            . $xp['volume'];

        $citation.= (!empty($xp['issue'])) ? '('. $xp['issue'] . '): ' : '';
        $citation.=  $xp['eloc'] . '. ' . $url_doi;

        return $citation;
    }

    /**
     * Get the first letters of the author's name
     * @param string $given_names
     * @return string
     */
    private function _get_first_letters($given_names) {
        $words = explode(" ", trim($given_names));
        $acronym = "";
        foreach ($words as $w) {
            if(preg_match('/[\w]/', $w[0])) {
                $acronym .= $w[0];
            }
        }
        return $acronym;
    }

    /**
     * Create the authors' list.
     *
     * @param DOMNodeList $_snames
     * @param DOMNodeList $_gnames
     * @return string
     */
    private function _make_authors_string(DOMNodeList $_snames, DOMNodeList $_gnames) {

        $authors = '';

        if(count($_snames) == 0) {
            return '';
        }

        foreach ($_snames as $entry) {
            $surnames[] = $entry->nodeValue;
        }

        foreach ($_gnames as $entry) {
            $gnames[] = $entry->nodeValue;
        }

        for($i=0; $i<count($surnames); $i++) {
            $authors.=$surnames[$i].' '.$this->_get_first_letters($gnames[$i]).", ";

            if($this->max_authors > 0 && ($i+1 == $this->max_authors)) {
                $authors.= 'et al.';
                break;
            }
        }

        if(substr($authors, -2, 2) == ', ') {
            $authors = substr($authors, 0, -2);
        }

        return $authors;

    }
}
