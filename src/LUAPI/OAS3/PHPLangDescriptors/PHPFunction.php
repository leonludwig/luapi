<?php
namespace LUAPI\OAS3\PHPLangDescriptors;

use LUAPI\OAS3\PHPLangDescriptors\PHPFunctionParameter;

class PHPFunction {
    public string $name;
    public string $returnType;
    public array $parameters;
    public string $body;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->parameters = array();
        $this->returnType = "";
    }

    public function addParameter(PHPFunctionParameter $parameter){
        array_push($this->parameters,$parameter);
    }

    public function getCode():string{
        if($this->returnType !== ""){
            $this->returnType = ":" . $this->returnType;
        }
        return ' 
        function '.$this->name.'('.$this->getParametersAsString().')'.$this->returnType.'{
            '.$this->body.'
        }
        ';
    }

    public function getBody():string{
        return $this->body;
    }

    private function getParametersAsString():string{
        $code = "";
        foreach($this->parameters as $parameter){
            $code .= $parameter->toString() . ', ';
        }
        if($code !== ""){
            $code = substr($code,0,strlen($code)-2);
        }

        return $code;
    }
}
?>