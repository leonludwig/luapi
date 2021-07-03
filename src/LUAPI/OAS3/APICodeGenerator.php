<?php
namespace LUAPI\OAS3;

use LUAPI\OAS3\PHPLangDescriptors\PHPFunction;
use LUAPI\OAS3\PHPLangDescriptors\PHPFunctionParameter;
use LUAPI\OAS3\PHPLangDescriptors\PHPSwitch;
use LUAPI\OAS3\PHPLangDescriptors\PHPSwitchCase;
use LUAPI\OAS3\PHPLangDescriptors\PHPClass;
use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use Exception;
use Throwable;

class APICodeGenerator {
    public OpenApi $apiDefinition;
    public array $handlers = array();

    /**
     * @param string $definitionFilePath realpath is needed for resolving references with relative Paths or URLs
     */
    public function __construct(string $definitionFilePath)
    {
        if(str_ends_with($definitionFilePath,".json")){
            $this->apiDefinition = Reader::readFromJsonFile($definitionFilePath);
        } else if(str_ends_with($definitionFilePath,".yaml")){
            $this->apiDefinition = Reader::readFromYamlFile($definitionFilePath);
        }
    }

    public function buildAPI(string $targetDirectory){
        if($this->isDirectoryEmpty($targetDirectory) == false){
            throw new Exception("Cannot build. Directory is not empty!");
            return;
        }

        //build directory tree
        foreach($this->apiDefinition->paths as $path => $definition){
            mkdir($targetDirectory . '/handlers/' . $this->removeParametersFromPath($path),0777,true);
        }

        //build the API Modules
        $this->generateAPIModules();

        foreach($this->handlers as $handler){
            $fileName = $handler->handlerName . '.php';
            $handlerPath = $targetDirectory . '/handlers/' . $this->removeParametersFromPath($handler->path);

            try{
                $fileHandle = fopen($handlerPath . '/' . $fileName,"w");
                fwrite($fileHandle,$handler->toNewHandlerDocument());
                fclose($fileHandle);
            } catch(Throwable $th){
                print($th->__toString());
                throw new Exception("Failed to create handler document for '$fileName'!",0,$th);
                return;
            }
        }
    }

    private function isDirectoryEmpty(string $dir):bool{
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
          if ($entry != "." && $entry != ".." && str_ends_with($entry,".json") == false && str_ends_with($entry,".yaml") == false) {
            closedir($handle);
            return FALSE;
          }
        }
        closedir($handle);
        return TRUE;
    }

    private function removeParametersFromPath(string $path){
        $path = preg_replace("/{([a-zA-Z\d]{1,})}/","",$path);
        $path = preg_replace('/\/\//', '/', $path);
        if(strpos($path,"?") !== false){
            $path = substr($path,0,strpos($path,"?")-1);
        }
        return $path;
    }

    public function generateAPIModules(){
        foreach($this->apiDefinition->paths as $path => $definition){
            $this->handlers[$path] = new APIHandlerCodeData($path,$definition);
        }
    }
}

class APIHandlerCodeData{
    public string $path;
    public PathItem $pathDefinition;

    public string $handlerBaseCode;
    public string $handlerName;

    public PHPSwitch $switchRequestMethod;
    public array $requestHandlingFunctions = array();
    public array $requestValidatorClasses = array();

    public function __construct(string $path, PathItem $pathDefinition)
    {
        $this->path = $path;
        $this->pathDefinition = $pathDefinition;
        $this->handlerBaseCode = file_get_contents(__DIR__ . '/BaseDocuments/handler.php');
        $this->buildHandler();
    }

    private function buildHandler(){
        $this->switchRequestMethod = new PHPSwitch('$request->getMethod()');
        $this->requestHandlingFunctions = array();
        $this->requestValidatorClasses = array();

        $this->loadName();
        $this->loadOperations();
    }

    private function loadName(){
        $handlerBaseName = $this->getHandlerNameForPath($this->path);
        $this->handlerName = $handlerBaseName . "Handler";
    }

    private function getHandlerNameForPath(string $filePath):string{
        $path = preg_replace("/{([a-zA-Z\d]{1,})}/","",$filePath);
        $path = preg_replace('/\/\//', '/', $path);
        if(strpos($path,"?") !== false){
            $path = substr($path,0,strpos($path,"?")-1);
        }
        $nameParts = preg_split('/\//', $path);

        $handlerName = "";
        foreach($nameParts as $word){
            $handlerName .= ucfirst($word);
        }

        return $handlerName;
    }

    private function loadOperations(){
        $handlerOperations = $this->pathDefinition->getOperations();
        foreach($handlerOperations as $methodName => $methodDefinition){
            $methodName = strtoupper($methodName);
            $functionName = "handle" . $methodName;
            if($methodDefinition->operationId !== ""){
                $functionName = $methodDefinition->operationId;
            }

            $switchCase = new PHPSwitchCase("'$methodName'",'
                $this->'.$functionName.'($request);
                return;
            ');
            $this->switchRequestMethod->addCase($switchCase);

            $validatorClassName = $functionName . "OAS3Validator";

            array_push($this->requestHandlingFunctions,$this->generateHandlerFunction($functionName,$validatorClassName));
            array_push($this->requestValidatorClasses,$this->generateValidatorClass($validatorClassName,$methodDefinition));
        }
    }

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

    private function generateValidatorClass(string $className, Operation $methodDefinition):PHPClass{
        $validator = new PHPClass($className,"OAS3Validator");
        $validateFunction = new PHPFunction("validateRequest");
        $validateFunction->returnType = "OAS3ValidationResult";
        $validationCode = "";

        foreach($methodDefinition->parameters as $parameter){
            $validationCode .= '
            $result = $this->validateParameter('.$this->stringifyParameter($parameter->name).','.$this->stringifyParameter($parameter->in).','.$this->boolToString($parameter->required).','.$this->boolToString($parameter->allowEmptyValue).','.$this->boolToString($parameter->allowReserved).','.$this->stringifyParameter(json_encode($parameter->schema->getSerializableData())).');
            if($result == false){
                return new OAS3ValidationResult(false,"parameter '.$parameter->name.' in '.$parameter->in.' does not match expected schema!");
            }
            ';
        }
        if($methodDefinition->requestBody->required && isset($methodDefinition->requestBody->content["application/json"])){
            $validationCode .= '
            $result = $this->validateBody('.$this->boolToString($methodDefinition->requestBody->required).','.$this->stringifyParameter(json_encode($methodDefinition->requestBody->content["application/json"]->getSerializableData())).');
            if($result == false){
                return new OAS3ValidationResult(false,"request body does not match expected schema!");
            }
            ';
        }
        $validationCode .= 'return new OAS3ValidationResult(true,"");';

        $validateFunction->body = $validationCode;
        $validator->addFunction($validateFunction);
        return $validator;
    }

    private function stringifyParameter($value){
        return "'$value'";
    }

    private function boolToString($value){
        if($value == true){
            return "true";
        }
        return "false";
    }

    public function toNewHandlerDocument():string{
        $code = $this->handlerBaseCode;
        $code = str_replace("__HANDLER_NAME__",$this->handlerName,$code);
        $code = str_replace("__SWITCH_METHODS__",$this->switchRequestMethod->toString(),$code);
        $code = str_replace("__METHOD_HANDLER_FUNCTIONS__",$this->handlerFunctionsToString(),$code);
        $code = str_replace("__VALIDATION_CLASSES__",$this->validationClassesToString(),$code);

        return $code;
    }

    private function handlerFunctionsToString():string{
        $code = "";
        foreach($this->requestHandlingFunctions as $handlingFunction){
            $code .= $handlingFunction->getCode() . "\r\n";
        }
        return $code;
    }

    private function validationClassesToString():string{
        $code = '//<luapi-gen id="validation-classes">' . "\r\n";
        foreach($this->requestValidatorClasses as $validatorClass){
            $code .= $validatorClass->toString() . "\r\n";
        }
        $code .= '//</luapi-gen>';
        return $code;
    }
}
?>