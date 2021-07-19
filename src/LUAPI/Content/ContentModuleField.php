<?php
namespace LUAPI\Content;

use Throwable;
/**
 * the description of a field inside a content module
 */
class ContentModuleField{
    public mixed $targetClass;
    public string $classPropertyName;
    public string $dbFieldName;
    public string $jsonPath;
    public string $evalBeforeInsert;
    public string $evalAfterGrab;
    public string $evalBeforeInsertJSON;
    public string $evalAfterGrabJSON;
    public bool $update;
    public bool $select;
    public bool $insert;

    /**
     * DO NOT USE THIS CONSTRUCTOR! USE ONE OF THE STATIC METHODS!
     */
    public function __construct(mixed $targetClass,string $classPropertyName,string $dbFieldName,string $jsonPath,
                                string $evalBeforeInsert,string $evalAfterGrab,string $evalBeforeInsertJSON,
                                string $evalAfterGrabJSON,bool $isUpdatable,bool $isSelectable, bool $isInsertable)
    {
        $this->targetClass = $targetClass;
        $this->classPropertyName = $classPropertyName;
        $this->dbFieldName = $dbFieldName;
        $this->jsonPath = $jsonPath;
        $this->evalBeforeInsert = $evalBeforeInsert;
        $this->evalAfterGrab = $evalAfterGrab;
        $this->evalBeforeInsertJSON = $evalBeforeInsertJSON;
        $this->evalAfterGrabJSON = $evalAfterGrabJSON;
        $this->update = $isUpdatable;
        $this->select = $isSelectable;
        $this->insert = $isInsertable;
    }

    /**
     * returns the store-type compatible representation of the current value
     * e.g. turns the DateTime value into a MySQL Timestamp or jsonSchema date-time value
     * @param string $for the storage type ("db" or "json")
     */
    public function getValue(string $for = "db"):mixed{
        $oValue = $this->targetClass->{$this->classPropertyName};

        if($for == "db" && $this->evalBeforeInsert !== ""){
            $oValue = $this->executeEval($this->evalBeforeInsert,$oValue);
        } if($for == "json" && $this->evalBeforeInsertJSON !== ""){
            $oValue = $this->executeEval($this->evalBeforeInsertJSON,$oValue);
        }
        return $oValue;
    }

    /**
     * sets the class field value to a PHP-Type compatible representation of the given value loaded from the storage type.
     * e.g. turns a MySQL timestamp to a DateTime Object
     * @param mixed $oValue the value to set the field to
     * @param string $from the storage type ("db" or "json")
     */
    public function setValue(mixed $oValue, string $from = "db"):void{
        if($from == "db" && $this->evalAfterGrab !== ""){
            $oValue = $this->executeEval($this->evalAfterGrab,$oValue);
        } else if($from == "json" && $this->evalAfterGrabJSON !== ""){
            $oValue = $this->executeEval($this->evalAfterGrabJSON,$oValue);
        }

        $this->targetClass->{$this->classPropertyName} = $oValue;
    }

    /**
     * executes a PHP code snipped
     * the wildcard "__value__" will be replaced by $oValue
     * @param string $eval the code snippet
     * @param mixed $oValue the value to insert into the snippet
     * @return mixed the eval result
     */
    private function executeEval(string $eval, mixed $oValue):mixed{
        $eval = str_replace("__value__",'$oValue',$eval);
        $eval = '$oValue = ' . $eval;
        try{
            eval($eval);
        } catch(Throwable $th){

        }
        return $oValue;
    }

    /**
     * creates a field information for a field whos value does not have to be processed after loading or storing it
     * @param mixed $targetClass the current object instance (containing the field)
     * @param string $classPropertyName the name of the property inside $targetClass
     * @param string $dbFieldName the name of the field inside the database
     * @param string $jsonPath the path to the field inside the JSON-representation of the object
     * @param bool $isUpdatable whether the value of this field can be updated inside the database
     * @param bool $isSelectable whether the value of this field can be loaded from the database
     * @param bool $isInsertable whether the value of this field can be inserted into the database
     */
    public static function noEval(mixed $targetClass,string $classPropertyName,string $dbFieldName,string $jsonPath,bool $isUpdatable,bool $isSelectable, bool $isInsertable):ContentModuleField{
        return new ContentModuleField($targetClass,$classPropertyName,$dbFieldName,$jsonPath,"","","","",$isUpdatable,$isSelectable,$isInsertable);
    }

    /**
     * creates a field information for a DateTime field
     * @param mixed $targetClass the current object instance (containing the field)
     * @param string $classPropertyName the name of the property inside $targetClass
     * @param string $dbFieldName the name of the field inside the database
     * @param string $jsonPath the path to the field inside the JSON-representation of the object
     * @param bool $isUpdatable whether the value of this field can be updated inside the database
     * @param bool $isSelectable whether the value of this field can be loaded from the database
     * @param bool $isInsertable whether the value of this field can be inserted into the database
     */
    public static function timestamp(mixed $targetClass,string $classPropertyName,string $dbFieldName,string $jsonPath,bool $isUpdatable,bool $isSelectable, bool $isInsertable):ContentModuleField{
        return new ContentModuleField($targetClass,$classPropertyName,$dbFieldName,$jsonPath,
        "__value__->format('Y-m-d H:i:s');",
        "DateTime::createFromFormat('Y-m-d H:i:s',__value__);",
        "__value__->format('Y-m-d H:i:s');",
        "DateTime::createFromFormat('Y-m-d H:i:s',__value__);",
        $isUpdatable,$isSelectable,$isInsertable);
    }

    /**
     * creates a field information for an array field
     * @param mixed $targetClass the current object instance (containing the field)
     * @param string $classPropertyName the name of the property inside $targetClass
     * @param string $dbFieldName the name of the field inside the database
     * @param string $jsonPath the path to the field inside the JSON-representation of the object
     * @param bool $isUpdatable whether the value of this field can be updated inside the database
     * @param bool $isSelectable whether the value of this field can be loaded from the database
     * @param bool $isInsertable whether the value of this field can be inserted into the database
     */
    public static function array(mixed $targetClass,string $classPropertyName,string $dbFieldName,string $jsonPath,bool $isUpdatable,bool $isSelectable, bool $isInsertable):ContentModuleField{
        return new ContentModuleField($targetClass,$classPropertyName,$dbFieldName,$jsonPath,
        "json_encode(__value__);",
        "json_decode(__value__);",
        "",
        "",
        $isUpdatable,$isSelectable,$isInsertable);
    }
}
?>