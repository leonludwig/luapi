<?php
namespace LUAPI\Database\Interfaces;

use LUAPI\Database\Connectors\PDOConnector;

/**
 * an abstract class to create an interface to a database
 */
abstract class SQLInterface {
    public PDOConnector $connector;

    public function __construct(PDOConnector $connector)
    {
        $this->connector = $connector;
    }

    abstract protected function tableExists(string $tableName):bool;

    /**
     * runs the given sql query and returns the fetch result.
     * @param string $sql the SQL query
     * @return mixed without error, the return value depends on the query. On any error, the value will always be FALSE.
     */
    public function queryAndFetch(string $sql):mixed{
        if($this->connector->pdo === null){
            return false;
        }

        try {
            $result = $this->connector->pdo->query($sql)->fetch();
        } catch (\Throwable $th) {
            $result = false;
        }
        return $result;
    }

    /**
     * runs the given prepared sql query with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @return mixed without error, the return value depends on the query. On any error, the value will always be FALSE.
     */
    public function queryAndFetchPrepared(string $sql, array $values):mixed{
        if($this->connector->pdo === null){
            return false;
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $statement->execute($values);
            $result = $statement->fetch();
        } catch (\Throwable $th) {
            $result = false;
        }
        return $result;
    }

    /**
     * runs the given prepared sql query with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @return array|bool an array of the rows in the resultset. On any error, the value will always be FALSE.
     */
    public function queryAndFetchAllPrepared(string $sql, array $values):mixed{
        if($this->connector->pdo === null){
            return false;
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $statement->execute($values);
            $result = $statement->fetchAll();
        } catch (\Throwable $th) {
            $result = false;
        }
        return $result;
    }

    /**
     * executes the given SQL-Statement
     * @param string $sql the SQL query
     * @return int|bool without error, the return value is the number of affected rows. On any error, the value will always be FALSE.
     */
    public function execute(string $sql):mixed{
        if($this->connector->pdo === null){
            return false;
        }

        try {
            $result = $this->connector->pdo->exec($sql);
        } catch (\Throwable $th) {
            $result = false;
        }
	    return $result;
    }

    /**
     * executes the given SQL-Statement with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @return bool whether the statement was executed successfully.
     */
    public function executePrepared(string $sql, array $values):bool{
        if($this->connector->pdo === null){
            return false;
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $result = $statement->execute($values);
        } catch (\Throwable $th) {
            $result = false;
        }
	    return $result;
    }

    /**
     * executes the given SQL-Statement with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @param string $columnName the column name to get the return value from
     * @return string without error, the return value is the value of pdo->lastInsertId($columnName). On any error, the value will always be FALSE.
     */
    public function executePreparedAndGetColumnValue(string $sql, array $values, string $columnName):string{
        if($this->connector->pdo === null){
            return false;
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $statement->execute($values);
            $result = $this->connector->pdo->lastInsertId($columnName);
        } catch (\Throwable $th) {
            $result = false;
        }
	    return $result;
    }
}
?>