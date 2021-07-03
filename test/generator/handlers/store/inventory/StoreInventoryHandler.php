<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class StoreInventoryHandler extends Handler{
    public function handle(Request $request)
    {
        switch($request->getMethod()){
case 'GET':
                    
                $this->getInventory($request);
                return;
            
                    break;
}

        $resp = new SimpleResponse();
        $resp->setDataAndSend(array(),"invalid method",$resp::HTTP_BAD_REQUEST);
    }

     
        function getInventory(Request $request):mixed{
            
        //<luapi-gen id="validation-getInventory">
        $validator = new getInventoryOAS3Validator($request);
        $validationResult = $validator->validateRequest();
        if($validationResult->validationSuccess == false){
            $resp = new SimpleResponse();
            $resp->setDataAndSend(array(),$validationResult->errorMessage,$resp::HTTP_BAD_REQUEST);
            return;
        }
        //</luapi-gen>
        
        }
        

}

//<luapi-gen id="validation-classes">
class getInventoryOAS3Validator extends OAS3Validator{ 
        function validateRequest():OAS3ValidationResult{
            return new OAS3ValidationResult(true,"");
        }
        
}
//</luapi-gen>
?>