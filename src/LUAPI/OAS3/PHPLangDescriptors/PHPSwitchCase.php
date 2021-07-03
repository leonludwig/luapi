<?php
namespace LUAPI\OAS3\PHPLangDescriptors;

class PHPSwitchCase {
    public string $compare;
    public string $body;

    public function __construct(string $compareValueWith, string $bodyCode)
    {
        $this->compare = $compareValueWith;
        $this->body = $bodyCode;
    }

    public function toString():string{
        
        return trim('
                case ' . $this->compare . ':
                    '.$this->body.'
                    break;
                ');
    }
}
?>