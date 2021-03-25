<?php
namespace LUAPI\Database\Connectors;

use LUAPI\Database\Connectors\PDOConnector;
use PDO;
use PDOException;
/**
 * a class that represents a PDO-based MySQL connection and provides useful functions.
 * It connects to a MySQL-Database defined in a config file. By default, the attributes PDO::ATTR_ERRMODE will be set to exception and emulated prepare statements will be disabled.
 * config file example:
 * {
 *      "connection":{
 *          "dbHost":"localhost",
 *          "dbName":"test",
 *          "dbCharset":"utf8mb4",
 *          "dbUsername":"root",
 *          "dbPassword":""
 *      }
 * }
 */
class MySQLConnector extends PDOConnector{
    /**
     * tries to connect to the databse specified in the config file.
     * @return bool whether the connection was established successful.
     */
    protected function createConnection():bool{
        $dbHost = $this->dbConfig["connection"]["dbHost"];
        $dbName = $this->dbConfig["connection"]["dbName"];
        $dbCharset = $this->dbConfig["connection"]["dbCharset"];
        $dbUsername = $this->dbConfig["connection"]["dbUsername"];
        $dbPassword = $this->dbConfig["connection"]["dbPassword"];
        try{
            $this->pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset", $dbUsername, $dbPassword);
            return true;
        }catch(PDOException $ex){
            $this->pdo = null;
            return false;
        }
    }

    /**
     * sets the PDO attributes, ist automatically called after the connection was successfully established.
     * you should override this function if you extend this class.
     */
    protected function setPDOAttributes():void{
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
}
?>