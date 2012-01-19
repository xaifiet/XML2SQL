<?php
/**
 * Settings class file
 *
 * PHP version 5
 *
 * @category  Settings
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
 * Class of setting loader
 *
 * @category  Settings
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   Release: @package_version@
 * @link      http://www.xaifiet.com
 * @see       INI
 * @since     Class available since Release 0.1
 */
class SettingsINI
{

    /**
     * Variable containing singleton resource of the class
     *
     * @var SettingsINI
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected static $SINGLETON;

    /**
     * Variable containing the list of the settings path
     *
     * The list can be completed with addPath() function
     *
     * @var array list of path in string
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $paths;

    /**
     * Variable containing all the settings
     *
     * @var array like ini settings se parse_ini_file
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $settings;

    /**
     * Singleton getter
     *
     * This function is used to get the current resource of the class
     *
     * @return SettingINI Seingleton resource
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
        $this->paths  = array();
        $this->addPath('config/');
        $this->settings = array();
    }

    /**
     * Add path function
     *
     * This function is used to add a path for settings files
     *
     * @param string $path New setttings path
     *
     * @return void
     *
     * @throw Exception Inexistant directory
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addPath($path)
    {
        if (!is_dir($path)) {
            throw new Exception('Directory '.$path.' does not exist');
        }
        $this->path[] = rtrim($path, '/').'/';
    }

    /**
     * Add setting file function
     *
     * This function is used to add a new setting file. All paths will be check to
     * find the appropriate file. If many files are found, all will be integrate by
     * path priority (last path add override firsts)
     *
     * @param string $file Name of the settings file
     *
     * @return void
     *
     * @throw Exception List of path empty
     * @throw Exception File not found in differents paths
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addFile($file)
    {
        if (!count($this->path)) {
            throw new Exception('List of path empty');
        }
        $flag = false;
        foreach ($this->path as $path) {
            $filepath = $path.$file;
            if (is_readable($filepath)) {
                $ini = parse_ini_file($filepath, true);
                $flag = true;
                $this->mergeSettings($this->settings, $ini);
            }
        }
        if (!$flag) {
            throw new Exception('File not found in differents paths');
        }
    }

    /**
     * Merging settings in currents settings
     *
     * This function merge new setting in current. For each value:
     * - If new setting is an array and current value no, the ini setting replace
     *   actual
     * - if new and old setting are array:
     *     - Add if new key is numeric and value empty
     *     - Empty setting if key is numeric and value empty
     *     - Replace if new key is string
     * - If new stting is a string 
     *
     * @param array &$settings current ini settings
     * @param array $ini       new ini settings
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function mergeSettings(&$settings, $ini)
    {
        if (is_array($ini)) {
            if (!is_array($settings)) {
                $settings = array();
            }
            foreach ($ini as $key => $value) {
                if (!is_string($key)) {
                    if (!strlen($value)) {
                        $settings = array();
                    } else {
                        $settings[] = $value;
                    }
                } else {
                    $this->mergeSettings($settings[$key], $value);
                }
            }
        } else {
            $settings = $ini;
        }
    }

    /**
     * Getter for settings values
     *
     * @param array $section ini section
     * @param array $field   ini field
     *
     * @return mixed value of the field
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getVariable($section, $field)
    {
        if (isset($this->settings[$section][$field])) {
            return $this->settings[$section][$field];
        }
        return false;
    }

}

?>
