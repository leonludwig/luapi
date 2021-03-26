<?php
namespace LUAPI\Validation;

class JSONSchemaBuilder {

    private array $data = array();

    public function  __construct()
    {
        
    }

    public function expect(string $level, SchemaItem $item)
    {
        $levels = preg_split('/[.]/', $level);
        
        $cLvlObject = $this->data;
        $cLvlIndex = 0;
        while($cLvlIndex < sizeof($levels)){
            if(array_key_exists("properties",$cLvlObject) == false){
                $cLvlObject = array(
                    "type" => "object",
                    "properties" => array()
                );
            }
            $cLvlIndex++;
        }

        array_push($cLvlObject["properties"],$item->toAssociativeArray());
    }

    public function toJSON():string{
        return json_encode($this->data);
    }
}

abstract class SchemaItem{
    /**
     * the name of the schema item/property
     */
    protected string $fieldName;
    /**
     * the properties of the schema item
     */
    protected array $properties;

    /**
     * implement this function to turn the properties & name to a json schema object
     */
    public abstract function toAssociativeArray():array;
}

/**
 * represents a Schema definition for "string" Visit: http://json-schema.org/understanding-json-schema/
 */
class SchemaString extends SchemaItem{
    const FORMAT_DATETIME = "date-time";
    const FORMAT_DATE = "time";
    const FORMAT_TIME = "date";
    const FORMAT_EMAIL = "email";
    const FORMAT_IDN_EMAIL = "idn-email";
    const FORMAT_HOSTNAME = "hostname";
    const FORMAT_IDN_HOSTNAME = "idn-hostname";
    const FORMAT_IPV4 = "ipv4";
    const FORMAT_IPV6 = "ipv6";
    const FORMAT_URI = "uri";
    const FORMAT_URI_REFERENCE = "uri-reference";
    const FORMAT_IRI = "iri";
    const FORMAT_IRI_REFERENCE = "iri-reference";

    /**
     * creates & initializes the object
     * @param string $fieldName the name of the field.
     * @param int $minLength the minimum length of the string. Set NULL to ignore.
     * @param int $maxLength the maximum length of the string. Set NULL to ignore.
     * @param string $pattern the regex pattern. Set NULL to ignore.
     * @param string $pattern the predefined format. One of thhe FORMAT_ constants. Set NULL to ignore.
     */
    public function __construct(string $fieldName, int $minLength = null, int $maxLength = null, string $pattern = null, string $format = null){
        $this->fieldName = $fieldName;
        $this->properties = array(
            "minLength" => $minLength,
            "maxLength" => $maxLength,
            "pattern" => $pattern,
            "format" => $format,
        );
    }

    /**
     * turns the data into jsonSchema compatible associative array
     * @return array the object
     */
    public function toAssociativeArray(): array
    {
        $ret = array("type" => "string");
        foreach($this->properties as $key => $value){
            if($this->properties[$key] !== null){
                $ret[$key] = $value;
            }
        }
        return array($this->fieldName => $ret);
    }
}

/**
 * represents a Schema definition for "number" or "integer" Visit: http://json-schema.org/understanding-json-schema/
 */
class SchemaNumber extends SchemaItem{
    private bool $isInteger;

    /**
     * creates & initializes the object
     * @param string $fieldName the name of the field.
     * @param bool $integer whether the type should be "integer" or "number". Set NULL to ignore.
     * @param int $multipleOf the given value muust be a multiple of this parameter. Set NULL to ignore.
     * @param int $min the value must be equal or greater than this parameter. Set NULL to ignore.
     * @param int $max the value must be equal or lower than this parameter. Set NULL to ignore.
     */
    public function __construct(string $fieldName, bool $integer = false, mixed $multipleOf = null, mixed $min = null, mixed $max = null)
    {
        $this->fieldName = $fieldName;
        $this->isInteger = $integer;
        $this->properties = array(
            "multipleOf" => $multipleOf,
            "minimum" => $min,
            "maximum" => $max
        );
    }

    /**
     * turns the data into jsonSchema compatible associative array
     * @return array the object
     */
    public function toAssociativeArray(): array
    {
        $ret = array();
        $ret["type"] = "number";
        if($this->isInteger){
            $ret["type"] = "integer";
        }

        foreach($this->properties as $key => $value){
            if($this->properties[$key] !== null){
                $ret[$key] = $value;
            }
        }
        return array($this->fieldName => $ret);
    }
}

/**
 * represents a Schema definition for "boolean" Visit: http://json-schema.org/understanding-json-schema/
 */
class SchemaBoolean extends SchemaItem{
    /**
     * creates & initializes the object
     * @param string $fieldName the name of the field.
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * turns the data into jsonSchema compatible associative array
     * @return array the object
     */
    public function toAssociativeArray(): array
    {
        $ret = array("type" => "boolean");
        return array($this->fieldName => $ret);
    }
}

/**
 * represents a Schema definition for "enum" Visit: http://json-schema.org/understanding-json-schema/
 */
class SchemaEnum extends SchemaItem{
    private string $enumType;
    private array $enumValues;

    /**
     * creates & initializes the object
     * @param string $fieldName the name of the field.
     * @param string $type 'string'|'number'|'integer'
     * @param array $values the allowed values.
     */
    public function __construct(string $fieldName, string $type, array $values)
    {
        $this->fieldName = $fieldName;
        $this->enumType = $type;
        $this->enumValues = $values;
    }

    /**
     * turns the data into jsonSchema compatible associative array
     * @return array the object
     */
    public function toAssociativeArray(): array
    {
        $ret = array(
            "type" => $this->enumType,
            "enum" => $this->enumValues
        );
        return array($this->fieldName => $ret);
    }
}
?>