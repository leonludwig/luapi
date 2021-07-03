<?php
namespace LUAPI\OAS3\PHPLangDescriptors;

class PHPFunctionParameter {
    public string $type;
    public string $name;

    public function __construct($type,$name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    public function toString():string{
        return $this->type . ' $' . $this->name;
    }
}
?>