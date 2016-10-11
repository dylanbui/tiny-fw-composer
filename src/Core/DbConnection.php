<?php
/**
 * Created by PhpStorm.
 * User: dylanbui
 * Date: 11/24/15
 * Time: 12:18 PM
 */

/**
 * @Singleton to create database connection
 */

namespace TinyFw\Core;

use TinyFw\Support\Config as ConfigSupport;

class DbConnection
{

    /**
     * Holds an array insance of self
     * @var $instance
     */
    private static $instances = array();

    /**
     *
     * the constructor is set to private so
     * so nobody can create a new instance using new
     *
     */
    private function __construct() {}

    /**
     *
     * Return DB instance or create intitial connection
     * @return object (PDO)
     * @access public
     *
     */
    public static function getInstance($config_name = 'database_master')
    {
        if (!isset(self::$instances[$config_name]))
        {
//            $config = Config::getInstance();
//            $hostname = $config->config_values[$config_name]['db_hostname'];
//            $db_name = $config->config_values[$config_name]['db_name'];
//            $db_password = $config->config_values[$config_name]['db_password'];
//            $db_username = $config->config_values[$config_name]['db_username'];
//            $db_port = $config->config_values[$config_name]['db_port'];

            $configDb = ConfigSupport::get($config_name);
            $db_driver = $configDb['db_driver'];
            $db_hostname = $configDb['db_hostname'];
            $db_name = $configDb['db_name'];
            $db_password = $configDb['db_password'];
            $db_username = $configDb['db_username'];
            $db_port = $configDb['db_port'];

            try {
                self::$instances[$config_name] = new Pdo($db_driver, $db_hostname, $db_port, $db_username, $db_password, $db_name);

            } catch (\Exception $ex)
            {
                echo 'Create Db - ERROR: ' . $ex->getMessage();
                exit();
            }
        }
        return self::$instances[$config_name];
    }

    /**
     *
     * Like the constructor, we make __clone private
     * so nobody can clone the instance
     *
     */
    private function __clone() {}

} // end of class

// --  --
// -- Tach ra de co the de dang thay the Driver Connection Database neu can thiet --
// --  --

class Pdo extends \PDO
{
    // Database statement object
    private $stmt;

    // Create a PDO object and connect to the database
    public function __construct($db_driver, $hostname, $port, $username, $password, $database)
    {
        try {

            if ($db_driver == 'sqlite')
                parent::__construct("$db_driver:$hostname");
            else
                parent::__construct("$db_driver:host=$hostname;port=$port;dbname=$database", $username, $password);
//            // Set some options
//            // Return rows found, not changed, during inserts/updates
//            PDO::MYSQL_ATTR_FOUND_ROWS => true,
//            // Emulate prepares, in case the database doesn't support it
//            PDO::ATTR_EMULATE_PREPARES => true,
//            // Have errors get reported as exceptions, easier to catch
//            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//            // Return associative arrays, good for JSON encoding
//            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->query("SET NAMES 'UTF8'");
        } catch(\PDOException $e) {
            echo 'PDOException: ' . $e->getMessage();
            exit();
        }
    }

    public function __destruct()
    {
        $this->stmt = null;
    }

    public function selectOneRow($sql, $data = array())
    {
        try {
            // Prepare the SQL statement
            $this->stmt = $this->prepare($sql);
            // Execute the statement
            if ($this->stmt->execute($data)) {
                // Return the selected data as an assoc array
                return $this->stmt->fetch(\PDO::FETCH_ASSOC);
            }
            return false;
        }
        catch (\PDOException $e) {
            echo 'PDOException: ' . $e->getMessage();
            echo '<br>Sql: ' . $sql;
            echo "<pre>";
            print_r($data);
            echo "</pre>";
            exit();
        }
    }

    public function query($sql, $data = array())
    {
        try {
            // Prepare the SQL statement
            $this->stmt = $this->prepare($sql);
            // Execute the statement
            if ($this->stmt->execute($data)) {
                // -- Only run when SELECT query --
                // Return the selected data as an assoc array
                if ($this->stmt->columnCount() > 0)
                    return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
                return true;
            }
            return false;
        } catch(\PDOException $e) {
            echo 'PDOException: ' . $e->getMessage();
            echo '<br>Sql: ' . $sql;
            echo "<pre>";
            print_r($data);
            echo "</pre>";
            exit();
        }
    }

    public function exec($sql, $data = array()) {
        try {
            // Prepare the SQL statement
            $this->stmt = $this->prepare($sql);
            // Execute the statement
            if ($this->stmt->execute($data)) {
                // Return the number of rows affected
                return $this->stmt->rowCount();
            }
            return false;
        } catch (\PDOException $e) {
            echo 'PDOException: ' . $e->getMessage();
            exit();
        }
    }


    // Perform an INSERT query
    public function insert($sql, $data = array()) {
        return $this->exec($sql, $data);
    }

    // Perform an UPDATE query
    public function update($sql, $data = array()) {
        return $this->exec($sql, $data);
    }

    // Perform a REPLACE query
    public function replace($sql, $data = array()) {
        return $this->exec($sql, $data);
    }

    // Perform a DELETE query
    public function delete($sql, $data = array()) {
        return $this->exec($sql, $data);
    }

    public function errno()
    {
        return $this->errorCode();
    }

    public function error()
    {
        return $this->errorCode();
    }

    public function escape($value)
    {
        return $this->quote($value);
    }

    public function countAffected()
    {
        return $this->stmt->rowCount();
    }

    public function getLastId()
    {
        return $this->lastInsertId();
    }

//    public function close()
//    {
//        $this->pdo = null;
//        $this->stmt = null;
//        return true;
//    }

    /**
     * This method is needed for prepared statements. They require
     * the data type of the field to be bound with "i" s", etc.
     * This function takes the input, determines what type it is,
     * and then updates the param_type.
     *
     * @param mixed $item Input to determine the type.
     *
     * @return string The joined parameter types.
     */
    protected function _determineType($item)
    {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';
                break;

            case 'boolean':
            case 'integer':
                return 'i';
                break;

            case 'blob':
                return 'b';
                break;

            case 'double':
                return 'd';
                break;
        }
        return '';
    }
}