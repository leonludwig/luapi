<?php
namespace LUAPI\OAS3\PHPLangDescriptors;

use LUAPI\OAS3\PHPLangDescriptors\PHPSwitchCase;

class PHPSwitch {
    public string $switchTo;
    public array $cases;

    public function __construct(string $switchTo)
    {
        $this->switchTo = $switchTo;
        $this->cases = array();
    }

    public function addCase(PHPSwitchCase $case){
        array_push($this->cases,$case);
    }

    public function toString():string{
        $code = 'switch('.$this->switchTo.'){' . "\r\n";
        foreach($this->cases as $case){
            $code .= $case->toString() . "\r\n";
        }
        return $code . '}';
    }
}
?>