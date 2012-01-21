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

    protected $trans;

    protected $files;

    protected $objects;

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
    public function translate($convert)
    {
        $this->objects = new StdClass();
    
        $this->trans = new XmlParser();
        $this->trans->loadFile('convert', $convert);

        // Databases connection
        $dbh = new DatabaseHandlers();
        $dbs = $this->trans->getXPathElements('convert', '/xml/databases/database');
        foreach ($dbs as $db) {
            $tag = $this->trans->getTag($db);
            $test = $dbh->addDatabase($tag->attributes->name, $tag->attributes);
        }

        // Files opening
        $this->files = new XmlParser();
        $files = $this->trans->getXPathElements('convert', '/xml/files/file');
        foreach ($files as $file) {
            $tag = $this->trans->getTag($file);
            $this->files->loadFile($tag->attributes->name, $tag->attributes->path);
        }

        $trads = $this->trans->getXPathElements('convert',
                                                '/xml/translations/translation');
        foreach ($trads as $trad) {
            $this->callActionFunc($trad, false, false);
        }

        unset($this->files);
    }

    /**
     * Fonction d'appel aux fonction d'action sur les balises
     *
     * Cette fonction est appelée pour chaque ligne du fichier xml de conversion
     * Le nom de la balise est utilisé pour appeler la fonction d'action.
     * La fonction d'action est constituée du nom de la balise sans les '-' suivi du mot 'Action'
     */
    protected function callActionFunc($element, $fname, $node) {
        $tag = $this->trans->getTag($element);
        $func = str_replace('-', '', $tag->name).'Action';
        if (!(method_exists($this, $func))) {
            throw new Exception($tag->name.' action is not implement');
        }
        else {
            $res = $this->$func($tag, $fname, $node);
        }
    }


    protected function translationAction($tag, $fname, $node)
    {
        $translation = $tag->attributes->name;
        $file        = $tag->attributes->file;
        $xpath       = $tag->attributes->xpath;

        $xmlChildren = $this->files->getXPathElements($file, $xpath);
        $xmlNb       = count($xmlChildren);

        PHPClient::progress('Translation '.$translation, 0, $xmlNb);

        $xmlCount = 0;
        foreach ($xmlChildren as $xmlChild) {
            $xmlCount++;
            
            foreach ($tag->getChildren() as $tagChild) {
                $this->callActionFunc($tagChild, $file, $xmlChild);
            }
            PHPClient::progress('Translation '.$translation, $xmlCount, $xmlNb);
        }
    }

    protected function objectcreateAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $dbname  = $tag->attributes->database;
        $table   = $tag->attributes->table;

        if (isset($this->objects->$objName)) {
            throw new Exception('Object name already exist');
        }
        $this->objects->$objName = new DatabaseObject($dbname, $table);
    }

    protected function objectvaluexmlAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $field   = $tag->attributes->field;
        $xpath   = $tag->attributes->xpath;
        
        if (!isset($this->objects->$objName)) {
            throw new Exception('No object defined for this name');
        }
        $value = $this->files->getXPathValue($fname, $xpath, $node);
        $this->objects->$objName->$field = $value;
        
    }

    protected function objectsaveAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;

        if (!isset($this->objects->$objName)) {
            throw new Exception('No object defined for this name');
        }

        $this->objects->$objName->save();
        unset($this->objects->$objName);
    }


}

?>
