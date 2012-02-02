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
     * Variable containing XmlParser instance for translation configuration file
     *
     * @var XmlParser
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $trans;

    /**
     * Variable containing the XmlParserHandlers instance for all the XML sources
     *
     * @var XmlParserHandlers
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $files;

    /**
     * Variable containing the list of the currents objects of translation
     *
     * @var StdClass
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $objects;

    /**
     * Variable containing the list of translation indexes
     *
     * @var StdClass
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $indexes;

    /**
     * Variable containing the list of translation rules
     *
     * @var XmlParser
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
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
        $truncs = $this->trans->getXPathElements('/xml/truncates/truncate');
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

        // Databases commit
        $dbh->commit();
    }

    /**
     * Current objects getter function
     *
     * This function search a current object by his name and return it. If not object
     * is available for the name the script will be stopped with a status 1.
     *
     * @param string $name Name of the object
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function &getObject($name)
    {
        if (!isset($this->objects->$name)) {
            echo 'Object '.$name.' already exists'.chr(10);
            exit(1);
        }

        return $this->objects->$name;
    }

    /**
     * Current objects unsetter function
     *
     * This function unset a current object by his name
     *
     * @param string $name Name of the object
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function unsetObject($name)
    {
        if (isset($this->objects->$name)) {
            unset($this->objects->$name);
        }
    }

    /**
     * Translation action call
     *
     * This function call the function for the translations. The current tag is 
     * instanciate in a XmlTag object to get the action.
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
            echo 'Action '.$tag->name.' is not implement';
            exit(1);
        } else {
            $res = $this->$func($tag, $fname, $node);
        }
    }

    /**
     * Translation action function
     *
     * This function start a new translation
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
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

    /**
     * Object creation action function
     *
     * This function create a new object for the given name. If the an object already
     * exists for this name, the function will display an error an exit the script
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectcreateAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $dbname  = $tag->attributes->database;
        $table   = $tag->attributes->table;

        if (isset($this->objects->$objName)) {
            echo 'Object '.$objNname.' already exist';
            exit(1);
        }
        $this->objects->$objName = new DatabaseObject($objName, $dbname, $table);
    }

    /**
     * Object xml value action function
     *
     * This function add a value to an object by search in current node
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectvaluexmlAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $field   = $tag->attributes->field;
        $xpath   = $tag->attributes->xpath;
        if (isset($tag->attributes->require)) {
            $require = $tag->attributes->require;
        } else {
            $require = false;
        }

        $object = &$this->getObject($objName);

        $value = $this->files->getXPathValue($fname, $xpath, $node);
        if ($require == "true" && $value === false) {
            echo 'Require value not found for '.$objName.'->'.$field.chr(10);
            exit(1);
        }
        $this->objects->$objName->$field = $value;
    }

    /**
     * Object save action function
     *
     * This function save an object
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectsaveAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;

        $object = &$this->getObject($objName);

        $object->save();
        $this->unsetObject($objName);
    }

    /**
     * Xml loop action function
     *
     * This function search for all matching xpath in current node and apply sub-tags
     * of translation for each match.
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
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

    /**
     * Object ID action function
     *
     * This function create an ID for an object
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectidAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $field   = $tag->attributes->field;

        $object = &$this->getObject($objName);

        $object->setID($field);
    }

    /**
     * Object distinct action function
     *
     * This function define distincts values of an object to define if the script
     * will insert or update the database
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectdistinctAction($tag, $fname, $node)
    {
        $objName = $tag->attributes->name;
        $fields  = $tag->attributes->fields;

        $object = &$this->getObject($objName);

        foreach (explode(',', $fields) as $field) {
            $object->addDistinct($field);
        }
    }

    /**
     * Object attach link action function
     *
     * This function link two objects with a middle table
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
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

        $parentObj = &$this->getObject($parentName);
        $childObj = &$this->getObject($childName);

        $parentObj->addLinkChild(
            $childObj, $table, $parentField, $childField,
            $linkParentField, $linkChildField
        );
        
        $this->unsetObject($childName);
    }

    /**
     * Object attach link in action function
     *
     * This function link two object with a parent object value in the child
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectattachlinkinAction($tag, $fname, $node)
    {
        $parentName      = $tag->attributes->parent;
        $parentField     = $tag->attributes->parentfield;
        $childName       = $tag->attributes->child;
        $childField      = $tag->attributes->childfield;

        $parentObj = &$this->getObject($parentName);
        $childObj = &$this->getObject($childName);

        $parentObj->addLinkInChild($childObj, $parentField, $childField);

        $this->unsetObject($childName);
    }

    /**
     * Object attach action function
     *
     * This function attach two object
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectattachAction($tag, $fname, $node)
    {
        $parentName      = $tag->attributes->parent;
        $childName       = $tag->attributes->child;

        $parentObj = &$this->getObject($parentName);
        $childObj = &$this->getObject($childName);

        $parentObj->addChild($childObj);

        $this->unsetObject($childName);
    }

    /**
     * Object value index action function
     *
     * This function search a value in an index from an xml value
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectvalueindexAction($tag, $fname, $node)
    {
        $name  = $tag->attributes->name;
        $field = $tag->attributes->field;
        $index = $tag->attributes->index;
        $use   = $tag->attributes->use;
        $xpath = $tag->attributes->xpath;
        if (isset($tag->attributes->require)) {
            $require = $tag->attributes->require;
        } else {
            $require = false;
        }

        $object = &$this->getObject($name);

        $value = null;
        if (isset($this->indexes->$index)) {
            $id = $this->files->getXPathValue($fname, $use, $node);
            if (isset($this->indexes->$index->indexes->$id)) {
                $indexFile = $this->indexes->$index->fname;
                $indexNode = $this->indexes->$index->indexes->$id;
                $value = $this->files->getXPathValue($indexFile, $xpath, $indexNode);

                if (isset($tag->attributes->rule)) {
                    $rule  = $tag->attributes->rule;
                    $value = $this->rules->$rule->$value;
                }
            }
        }

        if ($require == "true" && is_null($value)) {
            echo 'Require value not found for '.$name.'->'.$field.chr(10);
            exit(1);
        }

        $object->$field = $value;
    }

    /**
     * Index loop action function
     *
     * This function will loop on index content by his ID
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function indexloopAction($tag, $fname, $node)
    {
        $index = $tag->attributes->index;
        $use   = $tag->attributes->use;

        $id = $this->files->getXPathValue($fname, $use, $node);
        
        if (isset($this->indexes->$index->indexes->$id)) {
            $indexFile = $this->indexes->$index->fname;
            $indexNode = $this->indexes->$index->indexes->$id;

            foreach ($tag->getChildren() as $child) {
                $this->callActionFunc($child, $indexFile, $indexNode);
            }
        }
    }

    /**
     * Object value object action function
     *
     * This function search a value from an other current object fields
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectvalueobjectAction($tag, $fname, $node)
    {
        $name   = $tag->attributes->name;
        $field  = $tag->attributes->field;
        $source = $tag->attributes->source;
        $use    = $tag->attributes->use;
        if (isset($tag->attributes->require)) {
            $require = $tag->attributes->require;
        } else {
            $require = false;
        }

        $object = &$this->getObject($name);
        $other  = &$this->getObject($source);

        if ($require == "true" && $other->$use == false) {
            echo 'Require value not found for '.$name.'->'.$field.chr(10);
            exit(1);
        }

        $object->$field = $other->$use;
    }

    /**
     * Object value user action function
     *
     * This function define a user value to a object field
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectvalueuserAction($tag, $fname, $node)
    {
        $name   = $tag->attributes->name;
        $field  = $tag->attributes->field;
        $value = $tag->attributes->value;

        $object = &$this->getObject($name);

        $object->$field = $value;
    }

    /**
     * Object filter value action function
     *
     * This function add a filter for an object. This filter will be apply on save
     * action.
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectfiltervalueAction($tag, $fname, $node)
    {
        $name   = $tag->attributes->name;
        $field  = $tag->attributes->field;
        if (isset($tag->attributes->accept)) {
            $accept = explode(',', $tag->attributes->accept);
        } else {
            $accept = array();
        }
        if (isset($tag->attributes->refuse)) {
            $refuse = explode(',', $tag->attributes->refuse);
        } else {
            $refuse = array();
        }

        $object = &$this->getObject($name);

        $object->addConditionValue($field, $accept, $refuse);
    }

    /**
     * Object filter children action function
     *
     * This function add a filter on object children. This filter can be on
     * : Children values
     * : Children names
     * : Number of matching children
     *
     * @param XmlTag     $tag   Translation current tag element
     * @param string     $fname XML file name
     * @param DOMElement $node  XML file current tag element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function objectfilterchildrenAction($tag, $fname, $node)
    {
        $name  = $tag->attributes->name;
        $child = $tag->attributes->child;
        if (isset($tag->attributes->field)) {
            $field = $tag->attributes->field;
        } else {
            $field = null;
        }
        if (isset($tag->attributes->accept)) {
            $accept = explode(',', $tag->attributes->accept);
        } else {
            $accept = array();
        }
        if (isset($tag->attributes->refuse)) {
            $refuse = explode(',', $tag->attributes->refuse);
        } else {
            $refuse = array();
        }
        if (isset($tag->attributes->minOccurs)) {
            $min = $tag->attributes->minOccurs;
        } else {
            $min = null;
        }
        if (isset($tag->attributes->maxOccurs)) {
            $max = $tag->attributes->maxOccurs;
        } else {
            $max = null;
        }

        $object = &$this->getObject($name);

        $object->addConditionChild($child, $field, $accept, $refuse, $min, $max);
    }

    protected function objectvaluecryptAction($tag, $fname, $node)
    {
        $name      = $tag->attributes->name;
        $field     = $tag->attributes->field;
        $algorithm = $tag->attributes->algorithm;

        $object = &$this->getObject($name);
        if (is_null($object->$field)) {
            return false;
        }

        switch ($algorithm) {
            case 'sha1':
                $value = sha1($object->$field);
                break;
            case 'md5':
                $value = md5($object->$field);
                break;
        };

        $object->$field = $value;
    }

}

?>
