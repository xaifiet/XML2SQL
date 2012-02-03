<?php
/**
 * Database object class file
 *
 * PHP version 5
 *
 * @category  Database
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 1997-2005 The PHP Group
 * @license   Xaifiet Corp licence
 * @version   GIT: <git_id>
 * @link      http://www.xaifiet.com
 * @see       SQL
 * @since     File available since Release 0.1
 */

 /**
 * Class of Database object creation and insertion/update.
 *
 * @category  Database
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 1997-2005 The PHP Group
 * @license   Xaifiet Corp licence
 * @version   Release: @package_version@
 * @link      http://www.xaifiet.com
 * @see       SQL
 * @since     Class available since Release 0.1
 */
class DatabaseObject
{

    /**
     * Variable containing the list of the increments of decimal objects ID
     *
     * @var array associate array $idname => increment value
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected static $increments;

    /**
     * Variable containing the object name
     *
     * @var string Object name
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $name;

    /**
     * Variable containing name of the database handler
     *
     * @var string
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $database;

    /**
     * Variable containing name of the table
     *
     * @var string
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $table;

    /**
     * Variable containing the list of the ID od the object
     *
     * @var array
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $id;

    /**
     * Variable containing the list of the fields and their value
     *
     * @var array
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $fields;

    /**
     * Variable containing the list of the distincts fields
     *
     * This list will be use to insert or update databases rows
     *
     * @var array
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $distincts;

    /**
     * Variable containing the list of the conditions
     *
     * This list will contains all the condition for saving the object
     *
     * @var array
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $conditions;

    /**
     * Variable containing the list of the children
     *
     * @var array
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $children;

    /**
     * Constructor of the object
     *
     * The constructor initiate the differentes variables of the object:
     * - The table name in argument
     * - The list of IDs
     * - The list of increments (if not alreadey initiated)
     * - The list of fields
     *
     * @param string $name     Name of the object
     * @param string $database Name of the database
     * @param string $table    Name of the SQL table
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct($name, $database, $table)
    {
        $this->name       = $name;
        $this->database   = $database;
        $this->table      = $table;
        $this->id         = null;
        $this->distincts  = new StdClass();
        $this->fields     = new StdClass();
        $this->conditions = array();
        $this->children   = array();
        
        if (is_null(self::$increments)) {
            self::$increments = new StdClass();
        }
        if (!isset(self::$increments->$name)) {
            self::$increments->$name          = new StdClass();
            self::$increments->$name->current = 0;
            self::$increments->$name->ids     = new StdClass();
        }
    }

    /**
     * Object name getter function
     *
     * This function return the name of the current object
     *
     * @return string Object Name
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Value setter for the object fields
     *
     * This function is used to setthe value of a given field
     *
     * @param string $name  Field name
     * @param string $value Field value
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __set($name, $value)
    {
        $this->fields->$name = $value;
    }

    /**
     * Valuer getter of the fields
     *
     * this function is used to get the value of a giver field
     *
     * @param string $name Field name
     *
     * @return string Field value
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __get($name)
    {
        if (isset($this->fields->$name)) {
            return $this->fields->$name;
        }
        return null;
    }

    /**
     * ID setter for the object
     *
     * this function is used to define an ID for the object
     *
     * @param string $field ID name
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function setID($field)
    {
        $this->id = $field;
    }

    /**
     * Distincts field setter
     *
     * this function set the distinct field list for updating or insering objects
     *
     * @param string $field Name of the field
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addDistinct($field)
    {
        if (!isset($this->distincts->$field)) {
            $this->distincts->$field = null;
        }
    }

    /**
     * Add child function
     *
     * This function add a new child with no link
     *
     * @param DatabaseObject $obj Child object
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addChild($obj)
    {
        $child = new StdClass();
        
        $child->object = $obj;
        $child->link   = 'none';
        
        $this->children[] = $child;
    }

    /**
     * Links setter for the object
     *
     * SQL Schema contains links between tables that require a third table like:
     * - table Commands (idcommant, ...)
     * - table Products (idproduct, ...)
     * - table Command_Products (idcommand, idproduct)
     * Those links are declared with this function
     * An id must have been set to declare a link by using setID() function
     *
     * @param StdClass $obj      Object of the child
     * @param string   $table    Name of the foreign table
     * @param string   $field    Name of the object ID in foreign table
     * @param string   $foreign  Name of the foreign field on foreign table
     * @param string   $tField   Name of the link table field
     * @param string   $tForeign Name of the link table foreign field
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addLinkChild($obj, $table, $field, $foreign, $tField, $tForeign)
    {
        $child = new StdClass();

        $child->object   = $obj;
        $child->link     = 'out';
        $child->table    = $table;
        $child->field    = $field;
        $child->foreign  = $foreign;
        $child->tField   = $tField;
        $child->tForeign = $tForeign;

        $this->children[] = $child;
    }

    /**
     * Add link child function
     *
     * This function add a child with link in this child into the object
     *
     * @param DatabaseObject $obj         Child object
     * @param string         $parentField Parent field name
     * @param string         $childField  Child field name
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addLinkInChild($obj, $parentField, $childField)
    {
        $child = new StdClass();

        $child->object      = $obj;
        $child->link        = 'in';
        $child->parentField = $parentField;
        $child->childField  = $childField;

        $this->children[] = $child;
    
    }

    /**
     * Serialize function
     *
     * This function is used to serialize objects for only save new objects
     * The serialization using the fields name and value
     *
     * @return string serialize string of the object
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function serialize()
    {
        $tmp = array();
        $fields = get_object_vars($this->fields);
        foreach ($fields as $key => $value) {
            $tmp[] = $key.'$§£'.$value;
        }
        ksort($tmp);
        $serialize = implode('£§$', $tmp);
        return $serialize;
    }

    /**
     * Save function
     *
     * This function save the object into the database
     *
     * @return void
     *
     * @throw Exception No field for this distinct
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function save()
    {    
        // Conditions verification
        foreach ($this->conditions as $condition) {
            if (!$this->checkCondition($condition)) {
                return false;
            }
        }

        // ID search and set
        $flgInsert = true;
        if (!is_null($this->id)) {
            $idfield = $this->id;
            $serial = $this->serialize();
            $name = $this->name;
            if (isset(self::$increments->$name->ids->$serial)) {
                $flgInsert = false;
            } else {
                $id = self::$increments->$name->current++;
                self::$increments->$name->ids->$serial = $id;
            }
            $this->fields->$idfield = self::$increments->$name->ids->$serial;
        }

        if ($flgInsert) {
            // Distincts getter from fields
            $distincts = get_object_vars($this->distincts);
            foreach ($distincts as $field => &$distinct) {
                if (isset($this->fields->$field)) {
                    $distinct = $this->fields->$field;
                } else {
                    throw new Exception('No field for this distinct');
                }
            }

            // Database insertion or update
            $dbh = DatabaseHandlers::getInstance();
            $fields = get_object_vars($this->fields);
            $dbh->insupSQL(
                $this->database, 'both', $this->table, $fields, $distincts
            );
        }

        // Children save
        foreach ($this->children as $child) {
            if ($child->link == 'in') {
                $childField  = $child->childField;
                $parentField = $child->parentField;
                $child->object->$childField = $this->$parentField;
            }

            $save = $child->object->save();

            if ($save && $child->link == 'out') {
                $link = new DatabaseObject('link', $this->database, $child->table);

                $tField          = $child->tField;
                $field           = $child->field;
                $link->$tField   = $this->$field;
                $link->addDistinct($tField);

                $tForeign        = $child->tForeign;
                $foreign         = $child->foreign;
                $link->$tForeign = $child->object->$foreign;
                $link->addDistinct($tForeign);
                
                $link->save();
            }
        }
        return true;

    }

    /**
     * Add Condition value function
     *
     * This function add a condition on a value. This condition will be verify before
     * saving the object
     *
     * @param string $field  Field name
     * @param array  $accept List of accepted values
     * @param array  $refuse List of refused values
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addConditionValue($field, $accept, $refuse)
    {
        $condition = new StdClass();

        $condition->type   = 'value';
        $condition->field  = $field;
        $condition->accept = $accept;
        $condition->refuse = $refuse;

        $this->conditions[] = $condition;
    }

    /**
     * Add condition child function
     *
     * This function add a child condition. This condition will be verify before
     * saving the object
     *
     * @param string $child  Child name
     * @param string $field  Field name
     * @param array  $accept List of accepted values
     * @param array  $refuse List of refused values
     * @param string $min    Min child match
     * @param string $max    Max child match
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addConditionChild($child, $field, $accept, $refuse, $min, $max)
    {
        $condition = new StdClass();

        $condition->type   = 'children';
        $condition->child  = $child;
        $condition->field  = $field;
        $condition->accept = $accept;
        $condition->refuse = $refuse;
        $condition->min    = $min;
        $condition->max    = $max;

        $this->conditions[] = $condition;
    }

    /**
     * Check condition value function
     *
     * This function check a value condition
     *
     * @param StdClass $condition Condition
     *
     * @return boolean True if OK, False if KO
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function checkConditionValue($condition)
    {
        $field  = $condition->field;
        $accept = $condition->accept;
        $refuse = $condition->refuse;

        if (count($accept) && !in_array($this->$field, $accept)) {
            return false;
        }
        if (count($refuse) && in_array($this->$field, $refuse)) {
            return false;
        }
        return true;
    }

    /**
     * Check condition child function
     *
     * This function check a child condition
     *
     * @param StdClass $condition Condition
     *
     * @return boolean True if OK, False if KO
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function checkCondition($condition)
    {
        if ($condition->type == 'value') {
            if (!$this->checkConditionValue($condition)) {
                return false;
            }
        } else if ($condition->type == 'children') {
            $flgCount = 0;
            foreach ($this->children as $child) {
                if (!is_null($condition->field)) {
                    if ($child->object->checkConditionValue($condition)) {
                        $flgCount++;
                    }
                } else {
                    if ($child->object->getName() == $condition->child) {
                        $flgCount++;
                    }
                }
                if ($child->object->checkCondition($condition)) {
                    return true;
                }
            }
            $min = is_null($condition->min) ? 1 : $condition->min;
            $unbounded = count($this->children);
            $max = is_null($condition->max) ? $unbounded : $condition->max;
            if ($flgCount < $min || $flgCount > $max) {
                return false;
            }
        } else {
            throw new Exception('Unknow condition');
        }
        return true;
    }

    /**
     * Children values searcher function
     *
     * This function search values from children fields and return them into a array
     *
     * @param string $name  Name of the child object
     * @param string $field Name fo the child field
     *
     * @return array List of values
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getChildrenValues($name, $field)
    {
        $res = array();

        foreach ($this->children as $child) {
            $childres = $child->object->getChildrenValues($name, $field);
            $res = array_merge($res, $childres);
            if ($child->object->getName() == $name) {
                $res[] = $child->object->$field;
            }
        }

        return array_unique($res);
    }


}

?>
