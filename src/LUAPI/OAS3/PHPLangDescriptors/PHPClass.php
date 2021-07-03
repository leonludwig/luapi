<?php
namespace LUAPI\OAS3\PHPLangDescriptors;

class PHPClass {
    public string $className;
    public string $extends;
    public array $functions;

    public function __construct(string $className, string $extends){
        $this->functions = array();
        $this->className = $className;
        $this->extends = $extends;
    }

    public function addFunction(PHPFunction $function){
        array_push($this->functions,$function);
    }

    public function toString():string{
        if($this->extends == ""){
            $code = 'class ' . $this->className . '{';
        } else {
            $code = 'class ' . $this->className . ' extends ' . $this->extends . '{';
        }
        foreach($this->functions as $function){
            $code .= $function->getCode() . "\r\n";
        }
        return $code . "}";
    }
}
?>