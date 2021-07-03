<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class PetFindByTagsHandler extends Handler{
    public function handle(Request $request)
    {
        switch($request->getMethod()){
case 'GET':
                    
                $this->findPetsByTags($request);
                return;
            
                    break;
}

        $resp = new SimpleResponse();
        $resp->setDataAndSend(array(),"invalid method",$resp::HTTP_BAD_REQUEST);
    }

     
        function findPetsByTags(Request $request):mixed{
            
        //<luapi-gen id="validation-findPetsByTags">
        $validator = new findPetsByTagsOAS3Validator($request);
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
class findPetsByTagsOAS3Validator extends OAS3Validator{ 
        function validateRequest():OAS3ValidationResult{
            
            $result = $this->validateParameter('tags','query',true,false,false,'{"type":"array","items":{"type":"string"}}');
            if($result == false){
                return new OAS3ValidationResult(false,"parameter tags in query does not match expected schema!");
            }
            return new OAS3ValidationResult(true,"");
        }
        
}
//</luapi-gen>
?>