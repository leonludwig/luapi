<?php
class APIRoute{
    public string $baseRoute;
    public array $variables;
    public string $regexPattern;

    public function __construct(string $route)
    {
        $this->baseRoute = $this->removeTrailingSlash($route);
        $this->variables = $this->extractVariables();
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
        $pattern = preg_replace("/{([a-zA-Z\d]{1,})}/","(.*)",$this->baseRoute);
        //escape the slashes in the route to create a vallid pattern
        $pattern = str_replace("/","\/",$pattern);
        return "/$pattern/";
    }

    /**
     * checks whether the given uri matches this api route
     * @param string $uri the request uri
     */
    public function matchURI(string $uri):mixed{
        $uri = $this->removeTrailingSlash($uri);
        $matches = array(array(),array()); //build the default return value of an empty match
        preg_match_all($this->regexPattern,$uri,$matches);
        if(count($matches[0]) == 0){ //if its matching, the first array in matches will contain the whole uri
            return false;
        }
        
        $vars = array();
        $index = 1; //starting at 1 because first match is the whole sting
        foreach($this->variables as $varname){ 
            //add variable name & value from the match in uri to $vars
            $vars[$varname] = $matches[$index][0];
            $index++;
        }
        return $vars;
    }
}
?>