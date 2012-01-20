#!/usr/bin/php
<?php
/**
 * Xml2Sql file
 *
 * This file initiate classes and launch the integration
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
 * Autoload function
 *
 * This function load classes on the go by search in classes directory
 *
 * @param string $class Class name
 *
 * @return void
 *
 * @since 0.1
 * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 */
function __autoload($class)
{
    include 'classes/'.$class.'.class.php';
}

date_default_timezone_set('UTC');
error_reporting(-1);

$convert = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : false;
if (!$convert) {
    echo 'Usage '.$_SERVER['argv'][0].' CONVERT'.chr(10);
    exit(1);
}

$xml2sql = new Xml2Sql($convert);
//$xml2sql->convert();

exit(0);

?>
