<?php
namespace LUAPI\Database\Interfaces;

use LUAPI\Database\Connectors\PDOConnector;
use LUAPI\Exceptions\SQLInterfaceException;

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
     * @return mixed the return value depends on the query
     * @throws SQLInterfaceException
     */
    public function queryAndFetch(string $sql):mixed{
        if($this->connector->pdo === null){
            throw new SQLInterfaceException("pdo connector is null!");
        }

        try {
            $result = $this->connector->pdo->query($sql)->fetch();
            return $result;
        } catch (\Throwable $th) {
            throw new SQLInterfaceException("",$sql,$th);
        }
    }

    /**
     * runs the given prepared sql query with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @return mixed the return value depends on the query
     * @throws SQLInterfaceException
     */
    public function queryAndFetchPrepared(string $sql, array $values):mixed{
        if($this->connector->pdo === null){
            throw new SQLInterfaceException("pdo connector is null!");
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $statement->execute($values);
            $result = $statement->fetch();
            return $result;
        } catch (\Throwable $th) {
            throw new SQLInterfaceException("",$sql,$th);
        }
    }

    /**
     * runs the given prepared sql query with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @return array an array of the rows in the resultset
     * @throws SQLInterfaceException
     */
    public function queryAndFetchAllPrepared(string $sql, array $values):array{
        if($this->connector->pdo === null){
            throw new SQLInterfaceException("pdo connector is null!");
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $statement->execute($values);
            $result = $statement->fetchAll();
            return $result;
        } catch (\Throwable $th) {
            throw new SQLInterfaceException("",$sql,$th);
        }
    }

    /**
     * executes the given SQL-Statement
     * @param string $sql the SQL query
     * @return int the return value is the number of affected rows
     * @throws SQLInterfaceException
     */
    public function execute(string $sql):int{
        if($this->connector->pdo === null){
            throw new SQLInterfaceException("pdo connector is null!");
        }

        try {
            $result = $this->connector->pdo->exec($sql);
            return $result;
        } catch (\Throwable $th) {
            throw new SQLInterfaceException("",$sql,$th);
        }
    }

    /**
     * executes the given SQL-Statement with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @return bool whether the statement was executed successfully.
     * @throws SQLInterfaceException
     */
    public function executePrepared(string $sql, array $values):bool{
        if($this->connector->pdo === null){
            throw new SQLInterfaceException("pdo connector is null!");
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $result = $statement->execute($values);
            return $result;
        } catch (\Throwable $th) {
            throw new SQLInterfaceException("",$sql,$th);
        }
    }

    /**
     * executes the given SQL-Statement with the given values.
     * @param string $sql the SQL query
     * @param array $values the values to replace the wildcards in the query with
     * @param string $columnName the column name to get the return value from
     * @return string the return value is the value of pdo->lastInsertId($columnName)
     * @throws SQLInterfaceException
     */
    public function executePreparedAndGetColumnValue(string $sql, array $values, string $columnName):string{
        if($this->connector->pdo === null){
            throw new SQLInterfaceException("pdo connector is null!");
        }

        try {
            $statement = $this->connector->pdo->prepare($sql);
            $statement->execute($values);
            $result = $this->connector->pdo->lastInsertId($columnName);
            return $result;
        } catch (\Throwable $th) {
            throw new SQLInterfaceException("",$sql,$th);
        }
    }
}
?>