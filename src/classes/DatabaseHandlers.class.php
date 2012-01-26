<?php
/**
 * Database handlers class file
 *
 * PHP version 5
 *
 * @category  SQL
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
 * Multiple SQL Database handler class
 *
 * DatabaseHandlers class manage many database connection on many Sql drivers.
 * Database compatibility: PostGreSQL
 * SQL statement methods are avalabled:
 * - insupSQL()  : Insert or update rows
 * - countSQL()  : Count rows
 * - deleteSQL() : delete rows
 *
 * DatabaseHandlers example:
 * <code>
 * <?php
 *
 * // First database configuration
 * $paramsdbm = new StdClass();
 * $paramsdbm->pilote = 'mysql';
 * $paramsdbm->host   = 'localhost';
 * $paramsdbm->dbname = 'mysqlDB';
 *
 * // Second database configuration
 * $paramsdbp = new StdClass();
 * $paramsdbp->pilote = 'pgsql';
 * $paramsdbp->host   = 'localhost';
 * $paramsdbp->dbname = 'pgsqlDB';
 * 
 * // Database connections
 * $dbh = new DatabaseHandlers();
 * $dbh->addDatabase('sample1', $paramsdbm);
 * $dbh->addDatabase('sample2', $paramsdbp);
 *
 * // Database handlers singleton getter
 * $dbh = DatabaseHandlers::getInstance();
 *
 * // Database insertion row
 * $values = array('id' => 1, 'name' => 'myname');
 * $dbh->insupSQL('sample1', 'insert', 'table', $values);
 *
 * // Database update rows
 * $values = array('id' => 1, 'name' => 'newname');
 * $filter = array('id' => 1);
 * $dbh->insupSQL('sample1', 'update', 'table', $values);
 *
 * // Database insertion or update rows
 * $values = array('id' => 2, 'name' => 'myname');
 * $filter = array('id' => 2);
 * $dbh->insupSQL('sample1', 'both', 'table', $values, $filter);
 * $values = array('id' => 2, 'name' => 'newname');
 * $dbh->insupSQL('sample1', 'both', 'table', $values, $filter);
 *
 * // Database count rows
 * $values = array('id' => 2, 'name' => 'myname');
 * $filter = array('id' => 2);
 * $dbh->countSQL('sample1', 'table', $filter);
 *
 * // Database delete rows
 * $filter = array('id' => 2);
 * $dbh->deleteSQL('sample1', 'table', $filter);
 *
 * ?>
 * </code>
 *
 * @todo      Manage more SQL database compatibility (Mysql, SQL Server, ...)
 * @category  SQL
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   Release: @package_version@
 * @link      http://www.xaifiet.com
 * @see       INI
 * @since     Class available since Release 0.1
 */
class DatabaseHandlers
{

    /**
     * Class singleton variable
     *
     * The class have to be initialized by a new DatabaseHandlers to instantiate the
     * singleton parameter.
     *
     * @var DatabaseHandlers
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected static $SINGLETON;

    /**
     * Database handler array
     *
     * This value contain all the database handlers in a associated array:
     * - KEY   : name of the connection
     * - VALUE : StdClass (name   => name of the connection,
     *                     dsn    => Dsn of the connection,
     *                     handle => handle of the PDO connection
     *                    )
     *
     * @var array
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $db;


    /**
     * Singleton getter
     *
     * This function return the class singleton if initiated before
     *
     * @return DatabaseHandlers Singleton
     *
     * @throw Exception Database handlers not initialized
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public static function getInstance()
    {
        if (!isset(self::$SINGLETON)) {
            throw new Exception('Database Handler not iniatized');
        }
        return self::$SINGLETON;
    }

    /**
     * Class constructor
     *
     * The constructor set the singleton value and intiliaze the database handlers
     * array (empty).
     *
     * @return void
     *
     * @throw Exception Database Handlers already initialized
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct()
    {
        // Check if the class as already been initialized
        if (isset(self::$SINGLETON)) {
            throw new Exception('Database Handler already iniatize');
        }
        self::$SINGLETON = $this;

        // Initialize the database array handler
        $this->db = array();
    }

    /**
     * Class destructor
     *
     * The destructor make a roll-back if a transaction is available
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __destruct()
    {
        foreach ($this->db as $db) {
            if ($db->handle->inTransaction()) {
                $this->rollback($db->name);
            }
        }
    }

    /**
     * New database connection function
     *
     * This function add a new database connection in handlers. If the new database
     * name is already existing in current connections, an exception will be thrown.
     * The new connection well be added to the handle array with all informations
     *
     * @param string   $dbname Name of the connection
     * @param StdClass $param  Database connection parameters
     *
     * @return void
     *
     * @throw Exception Database name already used
     * @throw Exception Unknow database pilote
     * @throw Exception PDO connction error
     * @throw Exception PDO transaction error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addDatabase($dbname, $param)
    {
        $db = new StdClass();
    
        // Check the name of the database
        if (isset($this->db[$dbname])) {
            throw new Exception('Database name already used');
        }
        $db->name = $dbname;

        // Dsn for connection getter
        $pilote = isset($param->pilote) ? $param->pilote : '';
        if (!method_exists($this, $pilote.'DSN')) {
            throw new Exception('Unknow database pilote: '.$pilote);
        }
        $db->dsn = call_user_func(array($this, $pilote.'DSN'), $param);

        // Trying to connect to the database
        try {
            $db->handle = new PDO($db->dsn);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        // Transaction opening
        try {
            if (isset($param->transaction) && $param->transaction == true) {
                $db->handle->beginTransaction();
            }
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
        $this->db[$dbname] = $db;
    }

    /**
     * Database handle getter
     *
     * This function return the connection informations for the given name
     *
     * @param string $dbname Database connection name
     *
     * @return StdClass Database connection informations
     *
     * @throw Exception No connection for this name
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function getDBHandle($dbname)
    {
        if (!isset($this->db[$dbname])) {
            throw new Exception('No connection for this name');
        }
        return $this->db[$dbname];
    }

    /**
     * PostGreSQL dsn constructor
     *
     * This function generate the DSN for a PostGreSQL database
     *
     * @param array $param Database connction parameters
     *
     * @return void
     *
     * @throw Exception Invalid PGQSL DSN parameters
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function pgsqlDSN($param)
    {
        // Initialise dsn for PGSQL
        $dsn = 'pgsql:';
        
        // Getting the host parameter
        if (empty($param->host)) {
            throw new Exception('Host setting necessary for pgsql connection');
        }
        $dsn .= 'host='.$param->host.';';
        
        // Getting the port parameter
        $port = empty($param->port) ? '5432' : $param->port;
        $dsn .= 'port='.$port.';';
        
        // Getting the database name parameter
        if (empty($param->dbname)) {
            throw new Exception('Dabatase setting necessary for pgsql connection');
        }
        $dsn .= 'dbname='.$param->dbname.';';
        
        // Getting the username parameter
        if (!empty($param->user)) {
            $dsn .= 'user='.$param->user.';';
        }
        
        // Getting the password parameter
        if (!empty($param->password)) {
            $dsn .= 'password='.$param->password.';';
        }
        
        // return dsn string
        return $dsn;
    }

    /**
     * Roll back function
     *
     * this function make a roll back on the current transaction
     *
     * @param string $dbname Name of the connection
     *
     * @return Booleen True if ok
     *
     * @throw Exception No connection for this name
     * @throw Exception No transaction intiated for tis connection
     * @throw Excrption PDO rollback error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function rollback($dbname = null)
    {
        if (!is_null($dbname) ) {
            $dbs[$dbname] = $this->getDBHandle($dbname);
        } else {
            $dbs = $this->db;
        }

        foreach ($dbs as $db) {
            if ($db->handle->inTransaction()) {
                try {
                    $db->handle->rollback();
                } catch(PDOException $e) {
                    throw new Excpetion($s->getMessage());
                }
            }
        }
        return true;
    }

    /**
     * Commit function
     *
     * This function commit opened transactions for one connection if name parameter
     * given or all opened transactions if empty parameter
     *
     * @param string $dbname Name of the connection
     *
     * @return Boolean True if OK
     *
     * @throw Exception No connection for this name
     * @throw Excrption PDO commit error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function commit($dbname = null)
    {
        if (!is_null($dbname) ) {
            $dbs[$dbname] = $this->getDBHandle($dbname);
        } else {
            $dbs = $this->db;
        }
        
        foreach ($dbs as $db) {
            if ($db->handle->inTransaction()) {
                try {
                    $db->handle->commit();
                } catch(PDOException $e) {
                    throw new Excpetion($s->getMessage());
                }
            }
        }
        return true;
    }

    /**
     * SQL query function
     *
     * This function query the database with the given request
     *
     * @param string $dbname  Database connection name
     * @param string $request Request to query
     *
     * @return PDOStatement PDO request result
     *
     * @throw Exception PDO statement error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function querySql($dbname, $request)
    {
        $db = $this->getDBHandle($dbname);
        try {
            $res = $db->handle->query($request);
        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * SQL execution function
     *
     * This function execute the given request
     *
     * @param string $dbname  Database connection name
     * @param string $request Request to execute
     *
     * @return integer Number of modified line with request
     *
     * @throw Exception PDO statement error
     * @throw Exception PDO statement error informations
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function execSql($dbname, $request)
    {
        $db = $this->getDBHandle($dbname);
        try {
            $res = $db->handle->exec($request);
        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
        if ($res === false) {
            $error = $db->handle->errorInfo();
            throw new Exception($error[2]);
        }
        return $res;
    }

    /**
     * Quote values function
     *
     * This function add quotes to values for special characters
     * Use array_walk to change values
     *
     * @param string $dbname Database connection name
     * @param array  &$items Values to quote in array
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function addQuote($dbname, &$items)
    {
        $db = $this->getDBHandle($dbname);
        foreach ($items as &$item) {
            $item = $db->handle->quote($item);
        }
    }

    /**
     * Row count sql query
     *
     * This function return the number of row in a table by comparing fields and
     * values given
     *
     * @param string $dbname Database connection name
     * @param string $table  Table name
     * @param array  $filter Filter associated array (field => value)
     *
     * @return boolean False Incorrects parameters
     * @return integer Number of rows
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function countSQL($dbname, $table, $filter = array())
    {
        $this->addquote($dbname, $filter);
        foreach ($filter as $key => &$value) {
            $value = $key.'='.$value;
        }
        $request = 'SELECT * FROM '.$table;
        if (count($filter)) {
            $request .= ' WHERE '.implode(' AND ', $filter);
        }
        $query = $this->querySql($dbname, $request);
        if ($query === false) {
            return false;
        }
        return $query->rowCount();
    }

    /**
     * Row insertion and update function
     *
     * This function insert or update rows into database tables. The action parameter
     * define wich action the function will realise:
     * - insert : Insert the new row
     * - update : Update rows
     * - both   : Insert if no rows match with filter otherwise update rows
     *
     * @param string $dbname Database connection name
     * @param string $action Action to realize
     * @param string $table  Table name
     * @param array  $values Values associated array (field => value)
     * @param array  $filter Filter associated array (field => value)
     *
     * @return boolean False si KO
     * @return integer Number of changed rows
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function insupSql($dbname, $action, $table, $values, $filter = array())
    {
        if ($action == 'both') {
            if (count($filter)) {
                $count = $this->countSQL($dbname, $table, $filter);
                if (!is_numeric($count)) {
                    return false;
                }
                if ($count > 0) {
                    $action = 'update';
                } else {
                    $action = 'insert';
                }
            } else {
                $action = 'insert';
            }
        }
        $this->addquote($dbname, $values);
        $this->addquote($dbname, $filter);
        if ($action == 'insert') {
            $request = 'INSERT INTO '.$table.' (';
            $request .= implode(', ', array_keys($values));
            $request .= ') VALUES (';
            $request .= implode(', ', array_values($values));
            $request .= ');';
        } elseif ($action == 'update') {
            foreach ($values as $key => &$value) {
                $value = $key.'='.$value;
            }
            foreach ($filter as $key => &$value) {
                $value = $key.'='.$value;
            }
            $request = 'UPDATE '.$table.' SET ';
            $request .= implode(', ', $values);
            $request .= ' WHERE '.implode(' AND ', $filter);
            
        } else {
            return false;
        }
        return $this->execSql($dbname, $request);
    }

    /**
     * Delete sql function
     *
     * This function delete rows from a table. If filter is specified, only rows with
     * given values of fields are deleted.
     *
     * @param string $dbname Database connection name
     * @param string $table  Table name
     * @param array  $filter Filter associated array (field => value)
     *
     * @return integer Number of modified line with request
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function deleteSql($dbname, $table, $filter = array())
    {
        $this->addquote($dbname, $filter);
        $request = 'DELETE FROM '.$table;
        foreach ($filter as $key => &$value) {
            $value = $key.'='.$value;
        }
        $request = 'DELETE FROM '.$table;
        if (count($filter)) {
            $request .= ' WHERE '.implode(' AND ', $filter);
        }
        return $this->execSql($dbname, $request);
    }

}

?>
