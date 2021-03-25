<?php
namespace LUAPI;

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\APIRoute;

class API {
    /**
     * an array containing arrays of [route,handler]
     */
    private array $handlers = array();
    /**
     * the default handler used when no handler was found for a request uri
     */
    private ?Handler $defaultHandler = null;

    public function __construct()
    {
        
    }

    /**
     * add a handler for a specific request route.
     * @param string $route the api route. the api route: can contain variables noted like {varName}. Example: /api/user/{id}
     * @param Handler $handler the Handler that should handle requests to this api route
     * @return bool will return false if creating an APIRoute-Object for the given route string failed.
     */
    public function addHandler(string $route, Handler $handler):bool{
        try {
            $routeObj = new APIRoute($route); //try to create a route object.
        } catch (\Throwable $th) {
            $routeObj = null; //if it fails, set the object to null which will result in a return false
        }

        if($routeObj !== null){
            array_push($this->handlers,array($routeObj,$handler));
            return true;
        }
        return false;
    }

    /**
     * sets the default handler. used when no matching handler is found for a request URI.
     * @param Handler $handler the default handler. should not depend on any specific parameters or URL variables.
     */
    public function addDefaultHandler(Handler $handler):void{
        $this->defaultHandler = $handler;
    }

    /**
     * creates a request Object for the current request and tries to find the handler for it. if no matching handler is found it will try to use the default handler.
     * @return bool will return false if no matching handler was found and no default handler was specified.
     */
    public function handleRequest():bool{
        $uri = $_SERVER["REQUEST_URI"];
        $handler = null;

        //for each handler: check if its apiroute matches the request uri
        foreach($this->handlers as $routeAndHandler){
            $route = $routeAndHandler[0];
            $urlVars = $route->matchURI($uri); //returns false if no match, otherwise will be a list of the url variables
            if(is_array($urlVars)){ 
                $handler = $routeAndHandler[1];
                break;
            }
        }

        //no handler found? try to use the default one
        if($handler === null && $this->defaultHandler !== null){
            $handler = $this->defaultHandler;
        }
        //no default handler? return false
        if($handler === null){
            return false;
        }

        //create request and handle it
        if(is_array($urlVars) === false){ $urlVars = array(); } //for default handler (no variables)
        $req = new Request($urlVars);
        $handler->handle($req);
        return true;
    }
}
?>