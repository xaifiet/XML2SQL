<?php
/**
 * Database queries class file
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
 * SQL Database operation class
 *
 * This class is used to initiate the database connection and manage queries
 * Database compatibility: PostGreSQL
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
class DatabaseQuery
{

    /**
     * Database connction resource
     *
     * @var PDO
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $db = null;

    /**
     * Class constructor
     *
     * This function realise those operations :
     * - Get the connection DSN
     * - Connect to the database
     * - Initialise the transaction
     *
     * @param array $param Database connection parameters
     *
     * @return void
     *
     * @throw Exception Unknow database pilote
     * @throw Exception PDO connction error
     * @throw Exception PDO transaction error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct($param)
    {
        $pilote = isset($param->pilote) ? $param->pilote : '';
        if (!method_exists($this, $pilote.'DSN')) {
            throw new Exception('Unknow database pilote: '.$pilote);
        }
        $dsn = call_user_func(array($this, $pilote.'DSN'), $param);
        var_dump($dsn);
        // Tentative de connexion à la base de données
        try {
            $this->db = new PDO($dsn);
        }
        // Si erreur récupération de l'exception PDO et envoi d'une exception
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
        return;
        // Ouverture de la transaction si spécifié dans la configuration
        try {
            if (isset($param->transaction) && $param->transaction == true) {
                $this->db->beginTransaction();;
            }
        }
        // Si erreur récupération de l'exception PDO et envoi d'une exception
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
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
        if (!is_null($this->db) && $this->db->inTransaction()) {
            $this->rollback();
        }
    }

    /**
     * Roll back function
     *
     * this function make a roll back on the current transaction
     *
     * @return Booleen True if ok
     *
     * @throw Exception No transaction initiated
     * @throw Excrption PDO rollback error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function rollback()
    {
        if (!is_null($this->db) && $this->db->inTransaction()) {
            try {
                $this->db->rollBack();
            }
            catch(PDOException $e) {
                throw new Excpetion($s->getMessage());
            }
            return true;
        }
        throw new Exception('No transaction initiated');
    }

    /**
     * Fonction de commit
     *
     * This function commit queries in the transaction
     *
     * @param[out] Booleen True if OK
     *
     * @return void
     *
     * @throw Exception No transaction initiated
     * @throw Excrption PDO commit error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function commit()
    {
        if (!is_null($this->db) && $this->db->inTransaction()) {
            try {
                $this->db->commit();
            }
            catch(PDOException $e) {
                throw new Excpetion($s->getMessage());
            }
            return true;
        }
        throw new Exception('No transaction initiated');
    }

    /**
     * SQL query function
     *
     * This function query the database with the given request
     *
     * @param string $request Request to query
     *
     * @return PDOStatement PDO request result
     *
     * @throw Exception PDO statement error
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function querySql($request)
    {
        try {
            $res = $this->db->query($request);
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * SQL execution function
     *
     * This function execute the given request
     *
     * @param string $request Request to execute
     *
     * @return booleen True if OK and False if KO
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function execSql($request)
    {
        try {
            $res = $this->db->exec($request);
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
        if ($res === false) {
            $error = $this->db->errorInfo();
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
     * @param string &$item Value to quote
     * @param string $key   array_walk param
     *
     * @return void
     *
     * @exemple array_walk($values, array(&$this, 'addQuote'));
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected function addQuote(&$item, $key)
    {
        $item = $this->db->quote($item);
    }

    /**
     * Row count sql query
     *
     * This function return the number of row in a table by comparing fields and
     * values given
     *
     * @param string $table  Table name
     * @param array  $values Filter values
     * @param array  $ids    Filter identifiers
     *
     * @return boolean False Incorrects parameters
     * @return integer Number of rows
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function countSQL($table, array $values, array $ids)
    {
        $request = 'SELECT ';
        $request .= implode(', ', $ids);
        $request .= ' FROM '.$table.' WHERE ';
        $tmp = array();
        foreach ($ids as $id) {
            if (!isset($values[$id])) {
                return false;
            }
            $tmp[] = $id.'='.$values[$id];
        }
        $request .= implode(' AND ', $tmp);
        if (!$query = $this->querySql($request)) {
            return $query->rowCount();
        }
        return false;
    }

    /**
     * Row insertion function
     *
     * This function is used to insert a row into a table. If identifier are given
     * a verification of a present row is done. The insertion will be done if none
     * row has the same identifiers
     *
     * @param string $table   Table name
     * @param array  $values  Insertion values
     * @param array  $idcheck Check identifier before insertion
     *
     * @return boolean True si OK / False si KO
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function insertSql($table, array $values, array $idcheck = array())
    {
        array_walk($values, array(&$this, 'addQuote'));
        if (count($idcheck)) {
            $tmp = $this->countSQL($table, $values, $idcheck);
            if (is_numeric($tmp)) {
                if ($tmp > 0) {
                    return true;
                }
            } else {
                return false;
            }
        }
        $request = 'INSERT INTO '.$table.' (';
        $request .= implode(', ', array_keys($values));
        $request .= ') VALUES (';
        $request .= implode(', ', array_values($values));
        $request .= ');';
        return $this->execSql($request);
    }

    /**
     * Update sql function
     *
     * This function is used to update rows in a table
     *
     * @param string $table  Table name
     * @param array  $values Filter values
     * @param array  $id     Filter identifiers
     *
     * @return boolean True si OK / False si KO
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function updateSql($table, array $values, array $id)
    {
        array_walk($values, array(&$this, 'addQuote'));
        $update = array('id' => array(), 'value' => array());
        foreach ($values as $key => $value) {
            if (in_array($key, $id)) {
                array_push($update['id'], $key.' = '.$value);
            } else {
                array_push($update['value'], $key.' = '.$value);
            }
        }
        $request = 'UPDATE '.$table.' SET ';
        $request .= implode(', ', $update['value']);
        $request .= ' WHERE '.implode(', ', $update['id']);
        var_dump($request);
        return $this->execSql($request);
    }

    /**
     * Delete sql function
     *
     * This function is used to delete rows from a table
     *
     * @param string $table  Table name
     * @param array  $values Filter values
     * @param array  $ids    Filter identifiers
     *
     * @return boolean True si OK / False si KO
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function deleteSql($table, array $values = array(), array $ids = array())
    {
        array_walk($values, array(&$this, 'addQuote'));
        $tmp = array();
        foreach ($ids as $id) {
            if (!isset($values[$id])) {
                return false;
            }
            $tmp[] = $key.'='.$value;
        }
        $request = 'DELETE FROM '.$table;
        if (count($tmp)) {
            $request .= ' WHERE '.implode(' AND ', $tmp);
        }
        return $this->execSql($request);
    }

}

?>
