<?php
/**
 * Context class file
 *
 * PHP version 5
 *
 * @category  Context
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
 * Class of context
 *
 * This class is used to set all properties of running script
 *
 * @category  Context
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   Release: @package_version@
 * @link      http://www.xaifiet.com
 * @see       INI
 * @since     Class available since Release 0.1
 */
class Context
{

    /**
     * Variable containing singleton resource of the class
     *
     * @var SettingsINI
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected static $SINGLETON;

    /**
     * Variable containing context values
     *
     * The list can be completed with addValue() function
     *
     * @var array List of different values containing mixed data
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $values;

    /**
     * Singleton getter
     *
     * This function is used to get the current resource of the class
     *
     * @return Context Seingleton resource
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public static function getInstance()
    {
        $class = __CLASS__;
        if (is_null(self::$SINGLETON)) {
            self::$SINGLETON = new $class();
        }
        return self::$SINGLETON;
    }

    /**
     * Constructor of the object
     *
     * The constructor initiate the differentes variables of the object:
     * - The path list
     * - The settings values (empty array)
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct()
    {
        $this->values  = array();
    }

    /**
     * Add value function
     *
     * This function is used to add a new value in context
     *
     * @param string $section Name of the section
     * @param string $name    Name of the value
     * @param string $value   Value
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addValue($section, $name, $value)
    {
        $this->values[$section][$name] = $value;
    }

    /**
     * Getter for section
     *
     * @param string $section section name
     *
     * @return array section of the context
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getSection($section)
    {
        if (isset($this->values[$section])) {
            return $this->values[$section];
        }
        return false;
    }

    /**
     * Getter for values
     *
     * @param string $section section name
     * @param string $name    value name
     *
     * @return mixed value of the context
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getValue($section, $name)
    {
        if (isset($this->values[$section][$name])) {
            return $this->values[$section][$name];
        }
        return false;
    }

}

?>
