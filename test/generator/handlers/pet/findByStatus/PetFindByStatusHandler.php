<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class PetFindByStatusHandler extends Handler
{
	public function handle(Request $request)
	{
		switch ($request->getMethod()) {
case 'GET':
					
				$this->findPetsByStatus($request);
				return;
			
					break;
}

		$resp = new SimpleResponse();
		$resp->setDataAndSend(array(), "invalid method", $resp::HTTP_BAD_REQUEST);
	}

	 
	public function findPetsByStatus(Request $request):mixed
	{
			
		//<luapi-gen id="validation-findPetsByStatus">
		$validator = new findPetsByStatusOAS3Validator($request);
		$validationResult = $validator->validateRequest();
		if ($validationResult->validationSuccess == false) {
			$resp = new SimpleResponse();
			$resp->setDataAndSend(array(), $validationResult->errorMessage, $resp::HTTP_BAD_REQUEST);
			return;
		}
		//</luapi-gen>
	}
}

//<luapi-gen id="validation-classes">
class findPetsByStatusOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('status', 'query', true, false, false, '{"type":"array","items":{"enum":["available","pending","sold"],"type":"string","default":"available"}}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter status in query does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
//</luapi-gen>
