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
    protected $files;

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
     * The constructor initilize files value
     *
     * @return void
     *
     * @throw Exception File name already used
     * @throw Exception Inexistant or unreadable file
     * @throw Exception Load xml file failed
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct()
    {
        $this->files = new StdClass();
    }

    /**
     * Class constructor
     *
     * This function initiate the XML container by :
     * - Checking the configuration file (exist, readable)
     * - Loading the XML and check his validity
     *
     * @param string $fname Name of the file handle
     * @param string $path  Configuration file path
     *
     * @return void
     *
     * @throw Exception File name already used
     * @throw Exception Inexistant or unreadable file
     * @throw Exception Load xml file failed
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function loadFile($fname, $path)
    {
        // Check if the name is available
        if (isset($this->files->$fname)) {
            throw new Exception('File name already used');
        }

        // Instantiate the new file handle
        $newfile = new StdClass();
        $newfile->path = $path;

        // Check the read access to the xml file
        if (!is_readable($newfile->path)) {
            throw new Exception('Inexistant or unreadable file: '.$newfile->path);
        }

        // Load the xml file into the dom
        $newfile->dom = new DOMDocument();
        if (!$newfile->dom->load($newfile->path)) {
            throw new Exception('Load xml file failed : '.$newfile->filepath);
        }

        // Load the XPath
        $newfile->xpath = new DOMXPath($newfile->dom);
        // Load php function for XPath
        $newfile->xpath->registerNamespace('php', 'http://php.net/xpath');
        $newfile->xpath->registerPHPFunctions();

        // Insert the new filehandle in the class files
        $this->files->$fname = $newfile;
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
     * @param string     $fname Name of the file handle
     * @param string     $xpath XPath search string
     * @param DomElement $node  Root search node
     *
     * @return array List of DomElement node
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getXPathElements($fname, $xpath, $node = null)
    {
        if (!isset($this->files->$fname)) {
            throw new Exception('No file handle for this name');
        }
        if (is_null($node)) {
            $elements = $this->files->$fname->xpath->query($xpath);
        } else {
            $elements = $this->files->$fname->xpath->query($xpath, $node);
        }
        $res = array();
        foreach ($elements as $element) {
            $res[] = $element;
        }
        return $res;
    }

    /**
     * XPath value getter function
     *
     * This function the value from an xpath. If the xpath returns no result or more
     * than one, false will be returned
     *
     * @param string     $fname Name of the file handle
     * @param string     $xpath XPath search string
     * @param DomElement $node  Root search node
     *
     * @return mixed   Value of the XPath
     * @return boolean False if no value
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getXPathValue($fname, $xpath, $node)
    {
        $tmp = $this->getXPathElements($fname, $xpath, $node);
        if (!count($tmp)) {
            return false;
        } else if (count($tmp) > 1) {
            return false;
        }
        return $tmp[0]->value;
    }


}

?>
