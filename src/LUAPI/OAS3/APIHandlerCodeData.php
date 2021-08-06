<?php
namespace LUAPI\OAS3;

use LUAPI\OAS3\PHPLangDescriptors\PHPFunction;
use LUAPI\OAS3\PHPLangDescriptors\PHPFunctionParameter;
use LUAPI\OAS3\PHPLangDescriptors\PHPSwitch;
use LUAPI\OAS3\PHPLangDescriptors\PHPSwitchCase;
use LUAPI\OAS3\PHPLangDescriptors\PHPClass;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;

/**
 * a class describing an api handler and providing functionalities for generating code
 */
class APIHandlerCodeData{
    /**
     * the api path for the handler
     */
    public string $path;
    /**
     * the definition provided by the OAS3 defintion document
     */
    public PathItem $pathDefinition;
    /**
     * the path that contains vendor/autoload.php (empty when the vendor dir is in $targetDirectory)
     */
    public string $relativeVendorPath;

    /**
     * the base code (template) to use for code generation (see BaseDocuments/handler.php)
     */
    public string $handlerBaseCode;
    /**
     * the name for the resulting handler class
     */
    public string $handlerName;

    /**
     * objects for the generated code functions / sections
     */
    public PHPSwitch $switchRequestMethod;
    public array $requestHandlingFunctions = array();
    public array $requestValidatorClasses = array();

    /**
     * builds the object based on the provided data
     * @param string $path the api-path for the handler
     * @param string $pathDefinition the oas3-definition for this handler/path
     * @param string $relativeVendorPath the path that contains vendor/autoload.php (empty when the vendor dir is in $targetDirectory)
     */
    public function __construct(string $path, PathItem $pathDefinition, string $relativeVendorPath)
    {
        $this->path = $path;
        $this->pathDefinition = $pathDefinition;
        $this->relativeVendorPath = $relativeVendorPath;
        $this->handlerBaseCode = file_get_contents(__DIR__ . '/BaseDocuments/handler.php');
        $this->buildHandler();
    }

    /**
     * builds the code objects (functions and switch) and sets the name
     */
    private function buildHandler():void{
        $this->switchRequestMethod = new PHPSwitch('$request->getMethod()');
        $this->requestHandlingFunctions = array();
        $this->requestValidatorClasses = array();

        $this->loadName();
        $this->loadOperations();
    }

    /**
     * sets the name property based on the path. Example /user/login results in UserLoginHandler
     */
    private function loadName():void{
        $handlerBaseName = $this->getHandlerNameForPath($this->path);
        $this->handlerName = $handlerBaseName . "Handler";
    }

    /**
     * returns the basic handlername for the given path. Example /user/login results in UserLogin
     */
    private function getHandlerNameForPath(string $filePath):string{
        //remove parameter indicators (query and path)
        $path = str_replace("{","",$filePath);
        $path = str_replace("}","",$path);
        $path = preg_replace('/\/\//', '/', $path);
        if(strpos($path,"?") !== false){
            $path = substr($path,0,strpos($path,"?")-1);
        }
        //split in array by /
        $nameParts = preg_split('/\//', $path);

        //iterate through array and add every word with first letter upper case
        $handlerName = "";
        foreach($nameParts as $word){
            $handlerName .= ucfirst($word);
        }

        return $handlerName;
    }

    /**
     * loads every operation for this handler and creates the handling function and validation class and adds them to the property arrays
     * additionally sets the switch/case for the handler class
     */
    private function loadOperations():void{
        //get all operations
        $handlerOperations = $this->pathDefinition->getOperations();
        foreach($handlerOperations as $methodName => $methodDefinition){
            //create function name
            $methodName = strtoupper($methodName);
            $functionName = "handle" . $methodName;
            if($methodDefinition->operationId !== ""){
                $functionName = $methodDefinition->operationId;
            }

            //add new case to switch(requestMethod) property of this handler
            $switchCase = new PHPSwitchCase("'$methodName'",'
                $this->'.$functionName.'($request);
                return;
            ');
            $this->switchRequestMethod->addCase($switchCase);

            //create vlidator class name
            $validatorClassName = $functionName . "OAS3Validator";

            //generate handler function and validator and add them to the properties
            array_push($this->requestHandlingFunctions,$this->generateHandlerFunction($functionName,$validatorClassName));
            array_push($this->requestValidatorClasses,$this->generateValidatorClass($validatorClassName,$methodDefinition));
        }
    }

    /**
     * creates a handler function based on the provided names
     * (basically just inserts the names into a simple code template)
     */
    private function generateHandlerFunction(string $functionName, string $validatorClassName):PHPFunction{
        $function = new PHPFunction($functionName);
        $function->addParameter(new PHPFunctionParameter("Request","request"));
        $function->body = '
        //<luapi-gen id="validation-'.$functionName.'">
        $validator = new '.$validatorClassName.'($request);
        $validationResult = $validator->validateRequest();
        if($validationResult->validationSuccess == false){
            $resp = new SimpleResponse();
            $resp->setDataAndSend(array(),$validationResult->errorMessage,$resp::HTTP_BAD_REQUEST);
            return;
        }
        //</luapi-gen>
        ';

        return $function;
    }

    /**
     * generates the validtor class for the given operation/method
     * @param Operation $methodDefinition the definition to load the parameter validation info from
     */
    private function generateValidatorClass(string $className, Operation $methodDefinition):PHPClass{
        //create class and validate function
        $validator = new PHPClass($className,"OAS3Validator");
        $validateFunction = new PHPFunction("validateRequest");
        $validateFunction->returnType = "OAS3ValidationResult";
        $validationCode = "";

        //insert parameter information in the code template for parameter validation
        foreach($methodDefinition->parameters as $parameter){
            $validationCode .= '
            $result = $this->validateParameter('.$this->stringifyParameter($parameter->name).','.$this->stringifyParameter($parameter->in).','.$this->boolToString($parameter->required).','.$this->boolToString($parameter->allowEmptyValue).','.$this->boolToString($parameter->allowReserved).','.$this->stringifyParameter(json_encode($parameter->schema->getSerializableData())).');
            if($result == false){
                return new OAS3ValidationResult(false,"parameter '.$parameter->name.' in '.$parameter->in.' does not match expected schema!");
            }
            ';
        }
        //insert body validation info in code template
        if($methodDefinition->requestBody !== null && $methodDefinition->requestBody->required && isset($methodDefinition->requestBody->content["application/json"])){
            $validationCode .= '
            $result = $this->validateBody('.$this->boolToString($methodDefinition->requestBody->required).','.$this->stringifyParameter($this->getRequestBodySchema($methodDefinition->requestBody)).');
            if($result == false){
                return new OAS3ValidationResult(false,"request body does not match expected schema!");
            }
            ';
        }
        $validationCode .= 'return new OAS3ValidationResult(true,"");';

        //set function body and add function to validator class
        $validateFunction->body = $validationCode;
        $validator->addFunction($validateFunction);
        return $validator;
    }

    /**
     * returns a string that can be used as a string inside generated php code ('$value')
     */
    private function stringifyParameter($value):string{
        return "'$value'";
    }

    /**
     * returns the json schema for the given request body
     */
    private function getRequestBodySchema(\cebe\openapi\spec\RequestBody $requestBody):string{
        $schemaObject = $requestBody->content["application/json"]->getSerializableData();
        if(isset($schemaObject["schema"])){
            $schemaObject = $schemaObject["schema"];
        }
        return json_encode($schemaObject);
    }

    /**
     * turns a boolean into a string
     */
    private function boolToString($value):string{
        if($value == true){
            return "true";
        }
        return "false";
    }

    /**
     * creates the content of a new handler document for this object
     */
    public function toNewHandlerDocument():string{
        $code = $this->handlerBaseCode;
        $code = str_replace("__HANDLER_NAME__",$this->handlerName,$code);
        $code = str_replace("__SWITCH_METHODS__",$this->switchRequestMethod->toString(),$code);
        $code = str_replace("__METHOD_HANDLER_FUNCTIONS__",$this->handlerFunctionsToString(),$code);
        $code = str_replace("__VALIDATION_CLASSES__",$this->validationClassesToString(),$code);
        $code = str_replace("__DIR_UPS_TO_BASEPATH__",$this->getPathToAutoload(),$code);

        return $code;
    }

    /**
     * updates the content of an existing handler document to the current definition of the handler
     */
    public function updateHandlerDocument(string $documentContent):string{
        $documentContent = $this->replaceSectionUsingBaseCodeIfExists($documentContent,"require-autoload","__DIR_UPS_TO_BASEPATH__",$this->getPathToAutoload());
        $documentContent = $this->replaceSectionUsingBaseCodeIfExists($documentContent,"switch-methods","__SWITCH_METHODS__",$this->switchRequestMethod->toString());

        foreach($this->requestHandlingFunctions as $handlingFunction){
            $sectionID = "validation-".$handlingFunction->name;
            $documentContent = $this->replaceSectionWithoutBaseCodeIfExists($documentContent,$sectionID,$handlingFunction->body);
        }

        $documentContent = $this->replaceSectionUsingBaseCodeIfExists($documentContent,"validation-classes","__VALIDATION_CLASSES__",$this->validationClassesToString());

        return $documentContent;
    }

    /**
     * replaces a generator code section <luapi-gen id=""></luapi-gen> inside the document with the given content based on the same section in the handler base code
     * @param string $documentContent the document in which the section will be replaced
     * @param string $sectionID the id attribute of the section
     * @param string $placeholder the placeholder to replace inside the handler base code
     * @param string $content the content to insert inside the section
     */
    private function replaceSectionUsingBaseCodeIfExists(string $documentContent, string $sectionID, string $placeholder, string $content):string{
        if($this->generatorSectionExists($sectionID,$documentContent) == false){ return $documentContent; }

        $baseSection = $this->getGeneratorSection($sectionID,$this->handlerBaseCode);
        $existingSetion = $this->getGeneratorSection($sectionID,$documentContent);

        $replaceWith = str_replace($placeholder,$content,$baseSection);
        $documentContent = str_replace($existingSetion,$replaceWith,$documentContent);

        return $documentContent;
    }

    /**
     * replaces a generator code section <luapi-gen id=""></luapi-gen> inside the document with the given content 
     * @param string $documentContent the document in which the section will be replaced
     * @param string $sectionID the id attribute of the section
     * @param string $newContent the content to insert inside the section
     */
    private function replaceSectionWithoutBaseCodeIfExists(string $documentContent, string $sectionID, string $newContent):string{
        if($this->generatorSectionExists($sectionID,$documentContent) == false){ return $documentContent; }

        $existingSection = $this->getGeneratorSection($sectionID,$documentContent);

        $newContent = trim($newContent);
        $documentContent = str_replace($existingSection,$newContent,$documentContent);

        return $documentContent;
    }

    /**
     * gets a code section with the given id including the tags from the given document
     * @param string $id the section id
     * @param string $document the document to grab the section from
     */
    private function getGeneratorSection(string $id, string $document):string{
        $startTag = '//<luapi-gen id="'.$id.'">';
        $endTag = '//</luapi-gen>';

        $sectionStart = strpos($document,$startTag);
        $sectionEnd = strpos($document,$endTag,$sectionStart) + strlen($endTag);

        $section = substr($document,$sectionStart,$sectionEnd-$sectionStart);
        return $section;
    }

    /**
     * whether the given document contains a section with the given id
     */
    private function generatorSectionExists(string $id, string $document):bool{
        $startTag = '//<luapi-gen id="'.$id.'">';
        return str_contains($document,$startTag);
    }

    /**
     * returns the relative path to the autoload.php file
     */
    private function getPathToAutoload():string{
        $relativeAutoloadPath = "";

        $dirCount = substr_count($this->path,"/");
        if($this->relativeVendorPath !== ""){
            $dirCount++;
        }
        for($i = 0; $i < $dirCount; $i++){
            $relativeAutoloadPath .= "../";
        }

        if($this->relativeVendorPath !== ""){
            //remove last slash and add one in front
            $relativeAutoloadPath = "/" . substr($relativeAutoloadPath,0,strlen($relativeAutoloadPath)-1);
        }

        return $relativeAutoloadPath . $this->relativeVendorPath;
    }

    /**
     * returns a string containing the code of all handler functions inside the handler
     */
    private function handlerFunctionsToString():string{
        $code = "";
        foreach($this->requestHandlingFunctions as $handlingFunction){
            $code .= $handlingFunction->getCode() . "\r\n";
        }
        return $code;
    }

    /**
     * returns a string containing the code of all validation classes inside the handler
     */
    private function validationClassesToString():string{
        $code = "";
        foreach($this->requestValidatorClasses as $validatorClass){
            $code .= $validatorClass->toString() . "\r\n";
        }
        return $code;
    }
}
?>