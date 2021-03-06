<?php
namespace LUAPI;

/**
 * a class that represents an API route.
 * this class contains functions to extract the variableNames inside API-routes & create the regex patterns.
 */
class APIRoute{
    /**
     * the basic route string passed in to the constructor.
     */
    private string $baseRoute;
    /**
     * an array containing the names of the URL Variables in the Route 
     */
    private array $variableNames;
    /**
     * an array containing the values of the URL Variables in the Route 
     */
    private array $variableValues;
    /**
     * the regex pattern that is used to check if a given request URL matches this route.
     */
    private string $regexPattern;

    /**
     * creates & initializes the route object
     * @param string $route the api route: can contain variableNames noted like {varName}. Example: /api/user/{id}
     */
    public function __construct(string $route)
    {
        $this->baseRoute = $this->removeTrailingSlash($route);
        $this->variableNames = $this->extractVariables();
        $this->regexPattern = $this->buildRegexPattern();
    }

    /**
     * removes the trailing slash from the given uri
     * @param string $uri 
     * @return string $uri the uri without the trailing slash
     */
    private function removeTrailingSlash(string $uri):string{
        if(str_ends_with($uri,"/")){
            return substr($uri,0,strlen($uri)-1);
        }
        return $uri;
    }

    private function removeQuery(string $uri):string{
        if(str_contains($uri,"?")){
            return substr($uri,0,strpos($uri,"?"));
        }
        return $uri;
    }

    /**
     * extracts the variable names from the route
     * @return array the variable names
     */
    private function extractVariables():array{
        $matches = array(array(),array()); //build the default return value of an empty match
        preg_match_all("/{([a-zA-Z\d]{1,})}/",$this->baseRoute,$matches);
        return $matches[1];
    }

    /**
     * builds the regex match pattern for this route
     * @return string the regex pattern
     */
    private function buildRegexPattern():string{
        //replace the variable notations with a regex that matches anything between
        $pattern = preg_replace("/{([a-zA-Z\d]{1,})}/","(.*)",$this->removeTrailingSlash($this->baseRoute));
        //escape the slashes in the route to create a vallid pattern
        $pattern = str_replace("/","\/",$pattern);
        return "/$pattern/";
    }

    /**
     * checks whether the given uri matches this api route
     * @param string $uri the request uri
     */
    public function matchURI(string $uri):bool{
        $uri = $this->removeQuery($this->removeTrailingSlash($uri));
        $matches = array(array(),array()); //build the default return value of an empty match
        preg_match_all($this->regexPattern,$uri,$matches);

        if(count($matches[0]) == 0){ return false; } //not a single match!
        if(count($matches) == 1){ //1 match
            if($matches[0][0] != $uri){ return false; } //this can be a partly match so we return false if so
        }
        
        $this->variableValues = array();
        $index = 1; //starting at 1 because first match is the whole sting
        foreach($this->variableNames as $varname){ 
            //add variable name & value from the match in uri to $this->variableValues
            $this->variableValues[$varname] = $matches[$index][0];
            $index++;
        }
        return true;
    }
}
?>