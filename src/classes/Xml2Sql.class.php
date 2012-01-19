<?php
/**
 * Xml2Sql class file
 *
 * PHP version 5
 *
 * @category  XML,SQL
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   GIT: <git_id>
 * @link      http://www.xaifiet.com
 * @see       Xml2Sql
 * @since     File available since Release 0.1
 */

/**
 * Xml integration into SQL databases class
 *
 * This class integrate xml data into SQL databases
 *
 * @category  XML,SQL
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   Release: @package_version@
 * @link      http://www.xaifiet.com
 * @see       Xml2Sql
 * @since     Class available since Release 0.1
 */
class Xml2Sql
{

    /**
     * Class constructor
     *
     * This function initiate the integration by :
     * - Loading the XML integration configuration
     * - Establish databases connexion
     *
     * @param string $convert Integration configuration file path
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct($convert)
    {
        $xml = new XmlParser($convert);

        $dbs = $xml->getXPathElements('/xml/databases/database');
        foreach ($dbs as $db) {
            $tag = $xml->getTag($db);
            $test = new DatabaseQuery($tag->attributes);
        }
    }



}

?>
