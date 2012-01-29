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
    
    protected $indexes;
    
    protected $rules;

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

        $xsd = dirname($_SERVER['argv'][0]).'/xsd/Xml2Sql.xsd';

        $this->trans = new XmlParser($convert, $xsd);

        // Databases connection
        $dbh = new DatabaseHandlers();
        $dbs = $this->trans->getXPathElements('/xml/databases/*');
        foreach ($dbs as $db) {
            $tag = $this->trans->getTag($db);
            $tag->attributes->pilote = $tag->name;
            $test = $dbh->addDatabase($tag->attributes->name, $tag->attributes);
        }

        // Files opening
        $this->files = new XmlParserHandlers();
        $files = $this->trans->getXPathElements('/xml/files/file');
        foreach ($files as $file) {
            $tag = $this->trans->getTag($file);
            $fname = $tag->attributes->name;
            $fpath = $tag->attributes->path;
            $fxsd  = isset($tag->attributes->xsd) ? $tag->attributes->xsd : null;
            $this->files->loadFile($fname, $fpath, $fxsd);
        }

        // Indexes creation
        $this->indexes = new StdClass();
        $indexes = $this->trans->getXPathElements('/xml/indexes/index');
        foreach ($indexes as $index) {
            $tag = $this->trans->getTag($index);
            $indexName  = $tag->attributes->name;
            $indexFile  = $tag->attributes->file;
            $indexXPath = $tag->attributes->xpath;
            $indexUse   = $tag->attributes->use;
            $this->indexes->$indexName = new StdClass();
            $this->indexes->$indexName->fname = $indexFile;
            $this->indexes->$indexName->indexes = new StdClass;
            $values = $this->files->getXPathElements($indexFile, $indexXPath);
            foreach ($values as $value) {
                $id = $this->files->getXPathValue($indexFile, $indexUse, $value);
                $this->indexes->$indexName->indexes->$id = $value;
            }
        }

        // Table truncates
        $truncs = $this->trans->getXPathElements( '/xml/truncates/truncate');
        foreach ($truncs as $trunc) {
            $tag = $this->trans->getTag($trunc);
            $dbh->deleteSQL($tag->attributes->database, $tag->attributes->table);
        }

        // Rules
        $this->rules = new StdClass();
        $rules = $this->trans->getXPathElements('/xml/rules/rule');
        foreach ($rules as $rule) {
            $ruleName = $this->trans->getTag($rule)->attributes->name;
            $this->rules->$ruleName = new StdClass();
            $values = $this->trans->getXPathElements('value', $rule);
            foreach ($values as $value) {
                $tag = $this->trans->getTag($value);
                $code = $tag->attributes->code;
                $label = $tag->attributes->label;
                $this->rules->$ruleName->$code = $label;
            }
        
        }

        // Traductions
        $trads = $this->trans->getXPathElements('/xml/translations/translation');
        foreach ($trads as $trad) {
            $this->callActionFunc($trad, false, false);
        }

        unset($this->files);
        
        // Databases commit
        $dbh->commit();
    }

    /**
     * Fonction d'appel aux fonction d'action sur les balises
     *
     * Cette fonction est appelée pour chaque ligne du fichier xml de conversion
     * Le nom de la balise est utilisé pour appeler la fonction d'action.
     * La fonction d'action est constituée du nom de la balise sans les '-' suivi du
     * mot 'Action'
     *
     * @param DOMElement $element Translation current tag element
     * @param string     $fname   XML file name
     * @param DOMElement $node    XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function callActionFunc($element, $fname, $node)
    {
        $tag = $this->trans->getTag($element);
        $func = str_replace('-', '', $tag->name).'Action';
        if (!(method_exists($this, $func))) {
            throw new Exception($tag->name.' action is not implement');
        } else {
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
        $this->objects->$objName = new DatabaseObject($objName, $dbname, $table);
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

    protected function xmlloopAction($tag, $fname, $node)
    {
        $xpath = $tag->attributes->xpath;
        if (isset($tag->attributes->file)) {
            $fname = $tag->attributes->file;
            $node  = null;
        }
        $elements = $this->files->getXPathElements($fname, $xpath, $node);
        foreach ($elements as $element) {
            foreach ($tag->getChildren() as $child) {
                $this->callActionFunc($child, $fname, $element);
            }
        }
    }
    
    protected function objectidAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $field   = $tag->attributes->field;

        if (!isset($this->objects->$objName)) {
            throw new Exception('No object defined for this name');
        }
        
        $this->objects->$objName->setID($field);
    }
    
    protected function objectdistinctAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $fields  = $tag->attributes->fields;

        if (!isset($this->objects->$objName)) {
            throw new Exception('No object defined for this name');
        }
        
        foreach (explode(',', $fields) as $field) {
            $this->objects->$objName->addDistinct($field);
        }
    }

    protected function objectattachlinkAction($tag, $fname, $node)
    {
        $parentName  = $tag->attributes->parent;
        $parentField = $tag->attributes->parentfield;
        $childName   = $tag->attributes->child;
        $childField  = $tag->attributes->childfield;
        $table       = $tag->attributes->table;
        if (isset($tag->attributes->linkparentfield)) {
            $linkParentField = $tag->attributes->linkparentfield;
        } else {
            $linkParentField= $parentField;
        }
        if (isset($tag->attributes->linkchildfield)) {
            $linkChildField = $tag->attributes->linkchildfield;
        } else {
            $linkChildField = $childField;
        }
        
        $parentObj = $this->objects->$parentName;
        $childObj  = $this->objects->$childName;
        
        $parentObj->addLinkChild(
            $childObj, $table, $parentField, $childField,
            $linkParentField, $linkChildField
        );
        
        unset($this->objects->$childName);
    }

    protected function objectattachlinkinAction($tag, $fname, $node)
    {
        $parentName      = $tag->attributes->parent;
        $parentField     = $tag->attributes->parentfield;
        $childName       = $tag->attributes->child;
        $childField      = $tag->attributes->childfield;

        $parentObj = $this->objects->$parentName;
        $childObj  = $this->objects->$childName;

        $parentObj->addLinkInChild($childObj, $parentField, $childField);

        unset($this->objects->$childName);
    }

    protected function objectattachAction($tag, $fname, $node)
    {
        $parentName      = $tag->attributes->parent;
        $childName       = $tag->attributes->child;

        $parentObj = $this->objects->$parentName;
        $childObj  = $this->objects->$childName;
        
        $parentObj->addChild($childObj);
        
        unset($this->objects->$childName);

    }
    
    protected function objectvalueindexAction($tag, $fname, $node)
    {
        $name  = $tag->attributes->name;
        $field = $tag->attributes->field;
        $index = $tag->attributes->index;
        $use   = $tag->attributes->use;
        $xpath = $tag->attributes->xpath;
        
        if (!isset($this->objects->$name)) {
            throw new Exception('No object found');
        }
        if (!isset($this->indexes->$index)) {
            throw new Exception('No index found');
        }
        $id = $this->files->getXPathValue($fname, $use, $node);
        if (!isset($this->indexes->$index->indexes->$id)) {
            throw new Exception('No index id found');
        }
        $indexFile = $this->indexes->$index->fname;
        $indexNode = $this->indexes->$index->indexes->$id;
        $value = $this->files->getXPathValue($indexFile, $xpath, $indexNode);
        
        if (isset($tag->attributes->rule)) {
            $rule  = $tag->attributes->rule;
            $value = $this->rules->$rule->$value;
        }
        
        $this->objects->$name->$field = $value;
    }
    
    protected function indexloopAction($tag, $fname, $node)
    {
        $index = $tag->attributes->index;
        $use   = $tag->attributes->use;

        $id = $this->files->getXPathValue($fname, $use, $node);
        
        if (!isset($this->indexes->$index->indexes->$id)) {
            throw new Exception('No index id found');
        }

        $indexFile = $this->indexes->$index->fname;
        $indexNode = $this->indexes->$index->indexes->$id;

        foreach ($tag->getChildren() as $child) {
            $this->callActionFunc($child, $indexFile, $indexNode);
        }
    }

    protected function objectvalueobjectAction($tag, $fname, $node)
    {
        $name   = $tag->attributes->name;
        $field  = $tag->attributes->field;
        $object = $tag->attributes->object;
        $use    = $tag->attributes->use;

        if (!isset($this->objects->$name)) {
            throw new Exception('No object found');
        }
        if (!isset($this->objects->$object)) {
            throw new Exception('No object found');
        }
        
        $this->objects->$name->$field = $this->objects->$object->$use;

    }

}

?>
