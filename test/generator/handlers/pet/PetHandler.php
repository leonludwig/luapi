<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class PetHandler extends Handler
{
	public function handle(Request $request)
	{
		switch ($request->getMethod()) {

			case 'GET':
				$this->getPetById($request);
				return;
				break;

			case 'POST':
				$this->updatePetWithForm($request);
				return;
				break;

			case 'DELETE':
				$this->deletePet($request);
				return;
				break;
		}

		$resp = new SimpleResponse();
		$resp->setDataAndSend(array(), "invalid method", $resp::HTTP_BAD_REQUEST);
	}

	 
	public function getPetById(Request $request)
	{
			
		//<luapi-gen id="validation-getPetById">
		$validator = new getPetByIdOAS3Validator($request);
		$validationResult = $validator->validateRequest();
		if ($validationResult->validationSuccess == false) {
			$resp = new SimpleResponse();
			$resp->setDataAndSend(array(), $validationResult->errorMessage, $resp::HTTP_BAD_REQUEST);
			return;
		}
		//</luapi-gen>
	}
		
 
	public function updatePetWithForm(Request $request)
	{
			
		//<luapi-gen id="validation-updatePetWithForm">
		$validator = new updatePetWithFormOAS3Validator($request);
		$validationResult = $validator->validateRequest();
		if ($validationResult->validationSuccess == false) {
			$resp = new SimpleResponse();
			$resp->setDataAndSend(array(), $validationResult->errorMessage, $resp::HTTP_BAD_REQUEST);
			return;
		}
		//</luapi-gen>
	}
		
 
	public function deletePet(Request $request)
	{
			
		//<luapi-gen id="validation-deletePet">
		$validator = new deletePetOAS3Validator($request);
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
class getPetByIdOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('petId', 'path', true, false, false, '{"type":"integer","format":"int64"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter petId in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
class updatePetWithFormOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('petId', 'path', true, false, false, '{"type":"integer","format":"int64"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter petId in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
class deletePetOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('api_key', 'header', false, false, false, '{"type":"string"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter api_key in header does not match expected schema!");
		}
			
		$result = $this->validateParameter('petId', 'path', true, false, false, '{"type":"integer","format":"int64"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter petId in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
//</luapi-gen>
