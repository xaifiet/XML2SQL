<?php
/**
 * Xml parser class file
 *
 * PHP version 5
 *
 * @category  SQL
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   GIT: <git_id>
 * @link      http://www.xaifiet.com
 * @see       INI
 * @since     File available since Release 0.1
 */

/**
 * Xml parser class
 *
 * This class is used to parse xml files including XPath search
 *
 * @category  XML
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   Release: @package_version@
 * @link      http://www.xaifiet.com
 * @see       XML
 * @since     Class available since Release 0.1
 */
class XmlParser
{

    /**
     * DomDocument of the XML
     *
     * @var DomDocument
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $dom;

    /**
     * XPath of the XML
     *
     * @var DomXPath
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $xpath;

    /**
     * Class constructor
     *
     * This function initiate the XML container by :
     * - Checking the configuration file (exist, readable)
     * - Loading the XML and check his validity
     *
     * @param string $file Configuration file path
     *
     * @return void
     *
     * @throw Exception Inexistant or unreadable file
     * @throw Exception Load xml file failed
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct($file)
    {
        // Check the read access to the xml file
        if (!is_readable($file)) {
            throw new Exception('Inexistant or unreadable file: '.$file);
        }

        // Load the xml file into the dom
        $dom = new DOMDocument();
        if (!$dom->load($file)) {
            throw new Exception('Load xml file failed : '.$file);
        }
        $this->dom = $dom;

        // Load the XPath
        $this->xpath = new DOMXPath($this->dom);
        // Load php function for XPath
        $this->xpath->registerNamespace('php', 'http://php.net/xpath');
        $this->xpath->registerPHPFunctions();
    }

    /**
     * Get tag function
     *
     * This function get the tag information in XmlTag object
     *
     * @param DomElement $elem Element to get
     *
     * @return XmlTag Tag informations
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getTag(DOMElement $elem)
    {
        return new XmlTag($elem);
    }


    /**
     * XPath element getter function
     *
     * This function search xpath elements in Document or node id specified
     *
     * @param string     $xpath XPath search string
     * @param DomElement $node  Root search node
     *
     * @return array List of DomElement node
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getXPathElements($xpath, $node = null)
    {
        if (is_null($node)) {
            $elements = $this->xpath->query($xpath);
        } else {
            $elements = $this->xpath->query($path, $node);
        }
        $res = array();
        foreach ($elements as $element) {
            $res[] = $element;
        }
        return $res;
    }


}

?>
