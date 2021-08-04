<?php
namespace LUAPI\Content;

use LUAPI\Database\Connectors\PDOConnector;
use LUAPI\Content\ContentModuleField;
use LUAPI\Database\Interfaces\MySQLInterface;

/**
 * an abstract class that provides an interfaces for API/Program Content that is based on a simple database table or JSON Object.
 * you should call the initialize function from your child constructor and then rigter every class field.
 */
abstract class ContentModule{
    public string $dbTableName;
    public array $classFields;
    public string $keyFieldName;

    /**
     * initializes the object. You should call this method from the constructor of the child class.
     * @param string $dbTableName the name of the table where the data is stored in
     */
    public function initialize(string $dbTableName):void{
        $this->dbTableName = $dbTableName;
        $this->classFields = array();
    }

    /**
     * registers a new class field. You should register every class field that is set by the database / json.
     * @param ContentModuleField $field the field information
     * @param bool $isPrimaryKey whether the field is the identifier for writing / reading the object from the database
     */
    public function registerField(ContentModuleField $field, bool $isPrimaryKey):void{
        $this->classFields[$field->dbFieldName] = $field;
        if($isPrimaryKey){
            $this->keyFieldName = $field->dbFieldName;
        }
    }

    /**
     * creates the SELECT statement to grab the object from the database
     * @return array an array containing 0: the query text, 1: the values array for the prepared statement.
     */
    public function generateDBSelect():array{
        $arrayKey = ":".$this->keyFieldName;
        return array(
            "SELECT * FROM ".$this->dbTableName." WHERE ".$this->keyFieldName." = :".$this->keyFieldName, 
            array($arrayKey => $this->classFields[$this->keyFieldName]->getValue()));
    }

    /**
     * creates the INSERT statement to save the object to the database
     * @return array an array containing 0: the query text, 1: the values array for the prepared statement.
     */
    public function generateDBInsert():array{
        $sql = "INSERT INTO ".$this->dbTableName." (";

        //build field names section
        foreach($this->classFields as $fieldName => $field){
            if($field->insert){
                $sql .= "$fieldName,";
            }
        }
        $sql = substr($sql,0,strlen($sql)-1); // remove last comma

        $sql .= ") VALUES (";

        //build placeholder section
        foreach($this->classFields as $fieldName => $field){
            if($field->insert){
                $sql .= ":$fieldName,";
            }
        }
        $sql = substr($sql,0,strlen($sql)-1); // remove last comma

        $sql .= ")";

        //creates values array
        $values = array();
        foreach($this->classFields as $fieldName => $field){
            if($field->insert){
                $values[":$fieldName"] = $field->getValue();
            }
        }

        return array($sql,$values);
    }

    /**
     * creates the UPDATE statement to update the object in the database
     * @return array an array containing 0: the query text, 1: the values array for the prepared statement.
     */
    public function generateDBUpdate():array{
        $sql = "UPDATE ".$this->dbTableName." SET ";

        //create fields and placeholders section
        foreach($this->classFields as $fieldName => $field){
            if($field->update){
                $sql .= "$fieldName = :$fieldName,";
            }
        }
        $sql = substr($sql,0,strlen($sql)-1); // remove last comma

        $sql .= " WHERE ".$this->keyFieldName." = :".$this->keyFieldName;

        //create values array
        $values = array();
        foreach($this->classFields as $fieldName => $field){
            if($field->update){
                $values[":$fieldName"] = $field->getValue();
            }
        }
        $values[":".$this->keyFieldName] = $this->classFields[$this->keyFieldName]->getValue();

        return array($sql,$values);
    }

    /**
     * creates the DELETE statement to delete the object from the database
     * @return array an array containing 0: the query text, 1: the values array for the prepared statement.
     */
    public function generateDBDelete():array{
        $arrayKey = ":".$this->keyFieldName;
        return array(
            "DELETE FROM ".$this->dbTableName." WHERE ".$this->keyFieldName." = :".$this->keyFieldName, 
            array($arrayKey => $this->classFields[$this->keyFieldName]->getValue()));
    }

    /**
     * sets the values of the fields where isSelectable to the values from the given database row
     * if one value is not found in the database, the function will immediately return false
     * @param array $row an associated array returned by a DatabaseInterface
     * @return bool false if any field value could not be found in the database, true otherwise
     */
    public function setContentFromDBRow(array $row):bool{
        foreach($this->classFields as $fieldName => $field){
            if($field->select){
                if(isset($row[$fieldName]) == false){ return false; }
                $field->setValue($row[$fieldName]);
            }
        }
        return true;
    }

    /**
     * sets the values of the fields where a JSOn Path is specified to the value at the fields json path
     * @param array $jsonObject an ASSOCIATIVE array created by json_decode($jsonText,TRUE)
     */
    public function setContentFromObject(array $jsonObject):void{
        foreach($this->classFields as $fieldName => $field){
            if($field->jsonPath !== ""){
                $temp = $this->getAssocArrayValueAtPath($jsonObject,$field->jsonPath);
                if($temp !== null){
                    $field->setValue($temp,"json");
                }
            }
        }
    }

    /**
     * gets the value inside a nested object based on the given path separated by "/"
     * @param array $data the nested associative array containing the data
     * @param string $path the path to grab the value from. separated by "/"
     * @return mixed|null the value at the path or null
     */
    private function getAssocArrayValueAtPath(array $data, string $path):mixed {
        $temp = $data;
        foreach(explode("/", $path) as $ndx) {
            $temp = isset($temp[$ndx]) ? $temp[$ndx] : null;
        }
        return $temp;
    }

    /**
     * @return array an ssociative array built from the structure of the fields jsonPaths
     */
    public function toAssocArray():array{
        $ret = array();
        foreach($this->classFields as $fieldName => $field){
            if($field->jsonPath !== ""){
                $this->setAssocArrayValueAtPath($ret,$field->jsonPath,$field->getValue("json"));
            }
        }
        return $ret;
    }

    /**
     * sets a value at the given path inside the given associative array
     * @param array &$data the array to put the value in
     * @param string $path the path to the value
     * @param mixed $value the value to set
     */
    function setAssocArrayValueAtPath(array &$data, string $path, mixed $value):void{
        $temp = &$data;
        foreach(explode("/", $path) as $key) {
            $temp = &$temp[$key];
        }
        $temp = $value;
    }

    /**
     * MAKE SURE THE VALUE OF THE PRIMARY KEY FIELD IS SET BEFORE CALLING THIS
     * tries to load the content from the database and sets the values of the fields where isSelectable to the values from the given database row
     * if one value is not found in the database, the function will immediately return false
     * @param PDOConnector $dbConnector the connector to the database to load the content from
     * @return bool whether the content was loaded successfully
     */
    public function loadContentFromDatabase(PDOConnector $dbConnector):bool{
        $interface = new MySQLInterface($dbConnector);
        $cmdAndValues = $this->generateDBSelect();
        $result = $interface->queryAndFetchPrepared($cmdAndValues[0],$cmdAndValues[1]);
        if($result == false){
            return false;
        }
        return $this->setContentFromDBRow($result);
    }

    /**
     * MAKE SURE THE VALUE OF THE PRIMARY KEY FIELD IS SET BEFORE CALLING THIS
     * removes the object from the database.
     * @param PDOConnector $dbConnector the connector to the database to delete the object from
     * @return bool whether the object was deleted successfully
     */
    public function deleteFromDatabase(PDOConnector $dbConnector):bool{
        $interface = new MySQLInterface($dbConnector);
        $cmdAndValues = $this->generateDBDelete();
        $result = $interface->executePrepared($cmdAndValues[0],$cmdAndValues[1]);
        if($result == false){ return false; }
        return true;
    }

    /**
     * MAKE SURE THE VALUE OF THE PRIMARY KEY FIELD IS SET BEFORE CALLING THIS
     * saves the content of the object to the database
     * @param PDOConnector $dbConnector the connector to the database to save the object to
     * @return bool whether the object was deleted successfully
     */
    public function saveToDatabase(PDOConnector $dbConnector):mixed{
        $cmdAndValues = array();
        if($this->isNewElement()){
            $cmdAndValues = $this->generateDBInsert();
        } else {
            $cmdAndValues = $this->generateDBUpdate();
        }

        $interface = new MySQLInterface($dbConnector);
        $result = $interface->executePreparedAndGetColumnValue($cmdAndValues[0],$cmdAndValues[1],$this->keyFieldName);
        if($result == false){ return false; }
        if($this->isNewElement()){
            return $result;
        } else {
            return true;
        }
    }

    /**
     * @return bool false if the element has been saved to the database before
     */
    public abstract function isNewElement():bool;

    /**
     * MAKE SURE THE VALUE OF THE PRIMARY KEY FIELD IS SET BEFORE CALLING THIS
     * updates a single field of the object inside the database to the current value of the class field
     * @param PDOConnector $dbConnector the connector to the database to update the value at
     * @param string $fieldName the name of the classField to update
     * @return bool whether the field was updated successfully
     */
    public function updateSingleField(PDOConnector $dbConnector, string $fieldName):bool{
        $field = $this->classFields[$fieldName];
        $sql = "UPDATE ".$this->dbTableName." SET ".$field->dbFieldName." = :".$field->dbFieldName." WHERE ".$this->keyFieldName." = :".$this->keyFieldName;
        $values = array(":".$this->keyFieldName => $this->classFields[$this->keyFieldName]->getValue());

        $interface = new MySQLInterface($dbConnector);
        $result = $interface->executePrepared($sql,$values);

        if($result == false){
            return false;
        } else {
            return true;
        }
    }
}
?>