<?php
namespace LUAPI;

use Swaggest\JsonSchema\Schema;

/**
 * this class represents a request from a client. It provides simple interfaces to access request parameters.
 * For best practice you should use the bodyMatchesSchema-function to check if the client input matches your expectation.
 */
class Request {
    /**
     * an associative array containing the url parameters. similar to $_GET.
     */
    public array $queryParameters;
    /**
     * an associative array containing the url variables. defined in the API Route.
     */
    public array $pathParameters;
    /**
     * WARNING: LUAPI is built for APIs that use JSON bodies! Try to change your client to send the request with all parameters in a json body.
     * an associative array containing the parameters in the posted body (application/x-www-form-urlencoded). similar to $_POST.
     */
    public array $formParameters;
    /**
     * an associative array containing the parameters in the posted JSON body.
     */
    public array $bodyObject;


    /**
     * @param array $urlVars an associative array of the variables in the URI Route
     */
    public function __construct(array $urlVars)
    {
        $this->queryParameters = $_GET;
        $this->pathParameters = $urlVars;
        $this->formParameters = $_POST;
        $this->bodyObject = $this->getBodyObject();
    }

    /**
     * @return string the request method e.g. GET
     */
    public function getMethod():string{
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param string $expected
     * @return bool whether the request method matches your expectation (case insensitive)
     */
    public function isMethod(string $expected):bool{
        return strtolower($this->getMethod()) === strtolower($expected);
    }

    /**
     * @return string the raw request body. empty string on error.
     */
    public function getRawBody():string{
        try {
            $body = file_get_contents('php://input');
        } catch (\Throwable $th) {
            $body = "";
        }
        return $body;
    }

    /**
     * json-decodes the request body. will return an empty array on error.
     * @return array an associative array of the posted json
     */
    private function getBodyObject():array{
        try {
            $inputJSON = $this->getRawBody();
            $input = json_decode($inputJSON, TRUE);
            if($input === null){
                $input = array();
            }
        } catch (\Throwable $th) {
            $input = array();
        }
        return $input;
    }

    /**
     * returns the value at the given key inside the BODY OBJECT
     * @param string $key the name of the parameter
     * @return mixed|false the parameter value or false if not found
     */
    public function getParameter(string $key):mixed{
        if(isset($bodyObject[$key])){
            return $bodyObject[$key];
        }
        return false;
    }

    /**
     * synonym for getParameter.
     * @see getParamater
     */
    public function param(string $key):mixed{
        return $this->getParameter($key);
    }

    /**
     * returns the value of the given header.
     * @param string $key the header name. The key is automatically turned to upper case and - is replaced with _. if the key doesnt start with HTTP_, the method will add this prefix to the key.
     * @return mixed the header value
     */
    public function getHeader(string $key):mixed{
        $key = strtoupper($key);
        $key = str_replace("-","_",$key);
        if(str_starts_with($key,"HTTP_") == false){
            $key = "HTTP_" . $key;
        }

        return $_SERVER[$key];
    }

    /**
     * returns the value of the given cookie.
     * @param string $key the cookie name. " " and "." are automatically replaced with _.
     * @return mixed the cookie value 
     */
    public function getCookie(string $key):mixed{
        $key = str_replace(array(" ","."),"_",$key);
        return $_COOKIE[$key];
    }

    /**
     * creates a schema object from a json schema string. (check http://json-schema.org/ for more information)
     * @param string $jsonSchema the json schema string
     * @return Schema the Swaggest\JsonSchema\Schema object
     */
    public function createSchema(string $jsonSchema):Schema{
        return Schema::import(json_decode($jsonSchema));
    }

    /**
     * checks whether the body matches the given schema
     * implemented with https://github.com/opis/json-schema
     * @param Schema $schema - the json schema (check http://json-schema.org/ for more information)
     * @return true|Exception true or exception
     */
    public function bodyMatchesSchema(Schema $schema):mixed{
        try {
            $obj = $schema->in(json_decode($this->getRawBody()));
            return true;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
?>