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
        $this->distincts  = array();
        $this->fields     = array();
        $this->children   = array();
        
        if (is_null(self::$increments)) {
            self::$increments = new StdClass();
        }
        if (!isset(self::$increments->$name)) {
            self::$increments->$name = array();
        }
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
        $this->fields[$name] = $value;
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
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
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
        if (!in_array($field, $this->distincts)) {
            $this->distincts[$field] = "";
        }
    }

    public function addChild($obj)
    {
        $child = new StdClass();
        
        $child->object = $obj;
        $child->link   = false;
        
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
     * @param StdClass $object   Object of the child
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
        $child->link     = true;
        $child->table    = $table;
        $child->field    = $field;
        $child->foreign  = $foreign;
        $child->tField   = $tField;
        $child->tForeign = $tForeign;

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
        foreach ($this->fields as $key => $value) {
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
        // ID serach and set
        $flgInsert = true;
        if (!is_null($this->id)) {
            $serialize = $this->serialize();
            $objName = $this->name;
            $count = 0;
            foreach (self::$increments->$objName as $serial) {
                if ($serialize == $serial) {
                    $this->fields[$this->id] = $count;
                    $flgInsert = false;
                }
                $count++;
            }
            if ($flgInsert) {
                $this->fields[$this->id] = $count;
                array_push(self::$increments->$objName, $serialize);
            }
        }

        if ($flgInsert) {
            // Distincts getter from fields
            foreach ($this->distincts as $key => $distinct) {
                if (isset($this->fields[$key])) {
                    $this->distincts[$key] = $this->fields[$key];
                } else {
                    throw new Exception('No field for this distinct');
                }
            }

            // Database insertion or update
            $dbh = DatabaseHandlers::getInstance();
            $dbh->insupSQL(
                $this->database, 'both', $this->table,
                $this->fields, $this->distincts
            );
        }

        // Children save
        foreach ($this->children as $child) {
            $child->object->save();
            
            if ($child->link) {
                $link = new DatabaseObject('link', $this->database, $child->table);

                $tField          = $child->tField;
                $field           = $child->field;
                $link->$tField   = $this->$field;

                $tForeign        = $child->tForeign;
                $foreign         = $child->foreign;
                $link->$tForeign = $child->object->$foreign;

                $link->save();
            }
        }

    }

}

?>
