<?php
require 'Handler.php';
require 'Request.php';
require 'Response.php';
require 'APIRoute.php';

class API {
    private array $handlers = array();

    public function __construct()
    {
        
    }

    public function addHandler(string $route, Handler $handler):void{
        $routeObj = new APIRoute($route);
        array_push(array($routeObj,$handler));
    }

    public function handleRequest():bool{
        $uri = $_SERVER["REQUEST_URI"];
        $handler = null;

        foreach($this->handlers as $routeAndHandler){
            $route = $routeAndHandler[0];
            $urlVars = $route->matchURI($uri);
            if(is_array($urlVars)){
                $handler = $routeAndHandler[1];
                break;
            }
        }

        if($handler === null){
            return false;
        }
        $req = new Request($urlVars);
        $handler->handle($req);
        return true;
    }
}
?>