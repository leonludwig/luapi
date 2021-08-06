<?php
namespace LUAPI;

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\APIRoute;
use Throwable;

class API {
    /**
     * an associative array with the routes as keys and the sub-properties "path" and "className"
     */
    private array $handlers = array();
    /**
     * the default handler used when no handler was found for a request uri
     */
    private ?array $defaultHandler = null;

    public function __construct()
    {
        
    }

    /**
     * loads the handler information from the file at the given path. file should look like:
     * {
     *      "/handler/api/route":{
     *          "path":"/path/to/handler.php",
     *          "className":"NameHandler"
     *      }
     * }
     */
    public function loadHandlers(string $jsonFilePath):bool{
        try{
            $this->handlers = json_decode(file_get_contents($jsonFilePath),true);
            return true;
        } catch(Throwable $th){
            return false;
        }
    }

    /**
     * sets the default handler. used when no matching handler is found for a request URI.
     * @param string $handler the default handler className. should not depend on any specific parameters or URL variables.
     * @param string $path the path to the handler php file.
     */
    public function setDefaultHandlerNameAndPath(string $handler, string $path):void{
        $this->defaultHandler = array(
            "path" => $path,
            "className" => $handler
        );
    }

    /**
     * creates a request Object for the current request and tries to find the handler for it. if no matching handler is found it will try to use the default handler.
     * @return bool will return false if no matching handler was found and no default handler was specified.
     */
    public function handleRequest():bool{
        $uri = $_SERVER["REQUEST_URI"];
        $handlerData = null;
        $urlVars = null;

        //for each handler: check if its apiroute matches the request uri
        $tempHandlerData = null;
        $tempUrlVars = null;
        foreach($this->handlers as $handlerPath => $handlerInfo){
            $route = new APIRoute($handlerPath);
            $isMatch = $route->matchURI($uri);

            if($isMatch){ //is match
                $isMatchWithoutUrlVars = count($route->variableValues) == 0;
                $tempHandlerData = $handlerInfo;
                $tempUrlVars = $route->variableValues;
                if($isMatchWithoutUrlVars){ //we found the best possible match
                    break;
                }
                //otherwise, well take the last match we find
            }
        }

        $handlerData = $tempHandlerData;
        $urlVars = $tempUrlVars;

        if($handlerData == null && $this->defaultHandler == null){ //if handlerdata and defaulthandler are null, we cant handle the request
            return false;
        } else if($handlerData == null && is_array($this->defaultHandler)){ //if handlerdata is null and defaulthandler is array, we set handler to the default
            $handlerData = $this->defaultHandler;
        }

        if($handlerData["path"] !== ""){ //if path is specified, we have to include it
            include(getcwd() . $handlerData["path"]);
        }
        $handler = new $handlerData["className"](); //creating the handler based on its class name

        //create request and handle it
        if(is_array($urlVars) === false){ $urlVars = array(); } //for default handler (no variables)
        $req = new Request($urlVars);
        $handler->handle($req);
        return true;
    }
}
?>