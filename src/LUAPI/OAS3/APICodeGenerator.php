<?php
namespace LUAPI\OAS3;

use LUAPI\OAS3\APIHandlerCodeData;
use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
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

    /**
     * automatically generates LUAPI based PHP code in multiple handler documents for each api-path
     * @param string $targetDirectory the base directory of the project. a sub-directory "handlers" will be created
     * @param string $relativeVendorPath the path that contains vendor/autoload.php (empty when the vendor dir is in $targetDirectory)
     */
    public function buildAPI(string $targetDirectory, string $relativeVendorPath = ""):void{
        if($this->directoryAlreadyHasHandlersSubDir($targetDirectory)){
            throw new Exception("Cannot build. Handlers dir already existing!");
            return;
        }

        //build directory tree
        foreach($this->apiDefinition->paths as $path => $definition){
            mkdir($targetDirectory . '/handlers/' . $this->removeParametersFromPath($path),0777,true);
        }

        //build the API Modules
        $this->generateAPIModules($relativeVendorPath);

        foreach($this->handlers as $handler){
            $this->createHandler($handler,$targetDirectory);
        }
    }

    /**
     * updates the handlers created by the buildAPI() function. Will create handlers for paths that have been added to definition file.
     * generated handlers contain a comment with useful information about this function.
     * @param string $targetDirectory the base directory of the project (where the handlers sub directory is found)
     * @param string $relativeVendorPath the path that contains vendor/autoload.php (empty when the vendor dir is in $targetDirectory)
     */
    public function updateAPI(string $targetDirectory, string $relativeVendorPath = ""):void{
        if($this->directoryAlreadyHasHandlersSubDir($targetDirectory) == false){
            throw new Exception("Cannot update. Handlers dir not found!");
            return;
        }

        //build directory tree (if necessary)
        foreach($this->apiDefinition->paths as $path => $definition){
            if(file_exists($targetDirectory . '/handlers/' . $this->removeParametersFromPath($path)) == false){
                mkdir($targetDirectory . '/handlers/' . $this->removeParametersFromPath($path),0777,true);
            }
        }

        //build the API Modules
        $this->generateAPIModules($relativeVendorPath);

        foreach($this->handlers as $handler){
            $fileName = $handler->handlerName . '.php';
            $handlerPath = $targetDirectory . '/handlers/' . $this->removeParametersFromPath($handler->path);

            if(file_exists($handlerPath . '/' . $fileName)){
                $this->updateHandler($handler,$targetDirectory);
            } else {
                $this->createHandler($handler,$targetDirectory);
            }
        }
    }

    /**
     * creates a handler PHP document based on the given handler data
     * @param APIHandlerCodeData $handler the data / info of the handler to create
     * @param string $targetDirectory the base directory of the project
     */
    private function createHandler(APIHandlerCodeData $handler, string $targetDirectory):void{
        $fileName = $handler->handlerName . '.php';
        $handlerPath = $targetDirectory . '/handlers/' . $this->removeParametersFromPath($handler->path);

        try{
            $fileHandle = fopen($handlerPath . '/' . $fileName,"w");
            fwrite($fileHandle,$handler->toNewHandlerDocument());
            fclose($fileHandle);
        } catch(Throwable $th){
            print($th->__toString());
            throw new Exception("Failed to create handler document for '$fileName'!",0,$th);
        }
    }

    /**
     * updates a handler PHP document based on the given handler data
     * @param APIHandlerCodeData $handler the data / info of the handler to create
     * @param string $targetDirectory the base directory of the project
     */
    private function updateHandler(APIHandlerCodeData $handler, string $targetDirectory):void{
        $fileName = $handler->handlerName . '.php';
        $handlerPath = $targetDirectory . '/handlers/' . $this->removeParametersFromPath($handler->path);
        
        try{
            $existingDocument = file_get_contents($handlerPath . '/' . $fileName);
            $fileHandle = fopen($handlerPath . '/' . $fileName,"w");
            fwrite($fileHandle,$handler->updateHandlerDocument($existingDocument));
            fclose($fileHandle);
        } catch(Throwable $th){
            print($th->__toString());
            throw new Exception("Failed to create handler document for '$fileName'!",0,$th);
        }
    }

    /**
     * returns whether there is a sub directory called "handlers"
     * @param bool $dir the directory to look for "handlers" subdir in in
     */
    private function directoryAlreadyHasHandlersSubDir(string $dir):bool{
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry == "handlers") {
                closedir($handle);
                return true;
            }
        }
        closedir($handle);
        return false;
    }

    /**
     * removes possible parameters from the given path (parameters provided as "{parameterName}" or via query)
     * @param string $path the path
     */
    private function removeParametersFromPath(string $path):string{
        $path = preg_replace("/{([a-zA-Z\d]{1,})}/","",$path);
        $path = preg_replace('/\/\//', '/', $path);
        if(strpos($path,"?") !== false){
            $path = substr($path,0,strpos($path,"?")-1);
        }
        return $path;
    }

    /**
     * generates the APIHandlerCodeData objects for each api method and adds it to the handlers array
     * @param string $relativeVendorPath the path that contains vendor/autoload.php (empty when the vendor dir is in $targetDirectory)
     */
    public function generateAPIModules(string $relativeVendorPath):void{
        foreach($this->apiDefinition->paths as $path => $definition){
            $this->handlers[$path] = new APIHandlerCodeData($path,$definition,$relativeVendorPath);
        }
    }
}
?>