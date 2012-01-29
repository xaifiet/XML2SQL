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
class XmlParserHandlers
{

    /**
     * List of current opened files
     *
     * @var StdClass
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $files;

    /**
     * Class constructor
     *
     * The constructor initilize files value
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct()
    {
        $this->files = new StdClass();
    }

    /**
     * File loading function
     *
     * This function create a new XmlParser handle for the new file
     *
     * @param string $fname Name of the file handle
     * @param string $path  XML file path
     * @param string $xsd   XSD file path
     *
     * @return void
     *
     * @throw Exception File name already used
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function loadFile($fname, $path, $xsd = null)
    {
        // Check if the name is available
        if (isset($this->files->$fname)) {
            throw new Exception('File name already used');
        }

        // Instantiate the new file handle
        $newfile = new StdClass();
        $newfile->path = $path;

        $newfile->handle = new XmlParser($path, $xsd);

        // Insert the new filehandle in the class files
        $this->files->$fname = $newfile;
    }

    /**
     * Get tag function
     *
     * This function get the tag information in XmlTag object
     *
     * @param string     $fname Name of the file handle
     * @param DomElement $elem  Element to get
     *
     * @return XmlTag Tag informations
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getTag($fname, $elem)
    {
        if (!isset($this->files->$fname)) {
            throw new Exception('No file handle for this name');
        }
        return $this->files->$fname->handle->getTag($elem);
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
        return $this->files->$fname->handle->getXPathElements($xpath, $node);
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
        if (!isset($this->files->$fname)) {
            throw new Exception('No file handle for this name');
        }
        return $this->files->$fname->handle->getXPathValue($xpath, $node);
    }

}

?>
