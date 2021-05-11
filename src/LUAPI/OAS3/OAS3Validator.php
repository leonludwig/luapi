<?php
namespace LUAPI\OAS3;

use LUAPI\Request;
use cebe\openapi\Reader;
use cebe\openapi\spec\Schema;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Schema\Exception;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;

abstract class OAS3Validator {
    public Request $req;
    public SchemaValidator $validator;

    public function __construct(Request $req)
    {
        $this->req = $req;
        $validator = new SchemaValidator(SchemaValidator::VALIDATE_AS_REQUEST);
    }

    public function validateParameter(string $name, string $in, bool $required, bool $allowEmpty, bool $allowReserved, string $oas3Schema){
        if($in == "path"){
            return $this->validateURLParameter($name,$oas3Schema,$required);
        }
        if($in == "query"){
            return $this->validateQueryParameter($name,$oas3Schema,$required,$allowEmpty,$allowReserved);
        }
        if($in == "header"){
            return $this->validateHeaderParameter($name,$oas3Schema,$required);
        }
        if($in == "cookie"){
            return $this->validateCookieParameter($name,$oas3Schema,$required);
        }

        return false;
    }

    public function validateURLParameter(string $name, string $oas3Schema, bool $required):bool{
        if($required == false && !isset($this->req->urlVariables[$name])){
            return true;
        }

        $value = $this->req->urlVariables[$name];
        return $this->validateAgainstSchema($oas3Schema,$value);
    }

    public function validateQueryParameter(string $name, string $oas3Schema, bool $required, bool $allowEmpty, bool $allowReserved):bool{
        if($required == false && !isset($this->req->queryParameters[$name])){
            return true;
        }

        $value = $this->req->queryParameters[$name];
        return $this->validateAgainstSchema($oas3Schema,$value);
    }

    public function validateHeaderParameter(string $name, string $oas3Schema, bool $required):bool{
        $ignore = array("Accept","Content-Type","Authorization");
        if($required == false || in_array($name,$ignore)){
            return true;
        }

        $value = $this->req->getHeader($name);
        return $this->validateAgainstSchema($oas3Schema,$value);
    }

    public function validateCookieParameter(string $name, string $oas3Schema, bool $required):bool{
        if($required == false){
            return true;
        }

        $value = $this->req->getCookie($name);
        return $this->validateAgainstSchema($oas3Schema,$value);
    }

    public function validateBody(string $required, string $oas3Schema):bool{
        if($required == false){
            return true;
        }

        $value = $this->req->getRawBody();
        return $this->validateAgainstSchema($oas3Schema,$value);
    }

    public function validateAgainstSchema(string $oas3Schema, mixed $value):bool{
        $spec = Reader::readFromJson($oas3Schema);
        $schema = new Schema($spec->schema);
        try{
            $this->validator->validate($value,$schema);
            return true;
        } catch(SchemaMismatch $e){
            return false;
        }
    }


    public abstract function validateRequest():OAS3ValidationResult;
}

class OAS3ValidationResult{
    public bool $validationSuccess;
    public string $errorMessage;

    public function __construct(bool $success, string $errorMsg)
    {
        $this->validationSuccess = $success;
        $this->errorMessage = $errorMsg;
    }
}
?>