<?php
namespace LUAPI\Database\Interfaces;

use LUAPI\Database\Interfaces\SQLInterface;
/**
 * a class that represents an interface to a database connection.
 */
class MySQLInterface extends SQLInterface{
    /**
     * checks whether a table exists based on its table name.
     * @param string $tableName the name of the table to check
     * @return bool whether the table exists or not
     */
    public function tableExists(string $tableName):bool{
        if($this->connector->pdo === false){
            return false;
        }

        try {
            $result = $this->queryAndFetch("SELECT 1 FROM $tableName");
            $result = true;
        } catch (\Throwable $th) {
            $result = false;
        }
        return $result;
    }
}
?>