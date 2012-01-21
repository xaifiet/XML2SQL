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
    protected $ids;

    /**
     * Variable containing the list of the fields and their value
     *
     * @var array
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $fields;

    /**
     * Constructor of the object
     *
     * The constructor initiate the differentes variables of the object:
     * - The table name in argument
     * - The list of IDs
     * - The list of increments (if not alreadey initiated)
     * - The list of fields
     *
     * @param string $table Name of the SQL table
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct($database, $table)
    {
        $this->database   = $database;
        $this->table      = $table;
        $this->ids        = array();
        self::$increments = is_null(self::$increments) ? array() : self::$increments;
        $this->fields     = array();
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
     * @param string $value ID value (null by default)
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function setID($field, $value = null)
    {
        $this->ids[$field]['field'] = $field;
        if (is_null($value)) {
            if (!isset(self::$increments[$this->table])) {
                self::$increments[$this->table] = 0;
            }
            $this->ids[$field]['value'] = self::$increments[$this->table]++;
        } else {
            $this->ids[$field]['value'] = $value;
        }
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
     * @param string $id      Name of an existing ID of this object
     * @param string $table   Name of the foreign table
     * @param string $field   Name of the object ID in foreign table
     * @param string $foreign Name of the foreign field on foreign table
     * @param string $value   Value of the foreign field
     *
     * @return void
     *
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function setLink($id, $table, $field, $foreign, $value)
    {
        
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
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function save()
    {
        $dbh = DatabaseHandlers::getInstance();
        $dbh->insupSQL($this->database, 'insert', $this->table, $this->fields);
    }

}

?>
