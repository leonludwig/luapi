<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class __HANDLER_NAME__ extends Handler{
    public function handle(Request $request)
    {
        __SWITCH_METHODS__

        $resp = new SimpleResponse();
        $resp->setDataAndSend(array(),"invalid method",$resp::HTTP_BAD_REQUEST);
    }

    __METHOD_HANDLER_FUNCTIONS__
}

__VALIDATION_CLASSES__
?>