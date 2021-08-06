<?php
namespace LUAPI\Exceptions;

use Exception;

class RequestParameterNotFoundException extends Exception {
    public String $parameterName;
    public String $expectedParameterContainer;

    public function __construct(String $parameterName, String $expectedParameterContainer = "body")
    {
        $this->parameterName = $parameterName;
        $this->expectedParameterContainer = $expectedParameterContainer;
        parent::__construct("parameter {$this->parameterName} not found in {$this->expectedParameterContainer}!");
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
?>