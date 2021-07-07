<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class UserHandler extends Handler
{
	public function handle(Request $request)
	{
		switch ($request->getMethod()) {

			case 'GET':
				$this->getUserByName($request);
				return;
				break;

			case 'PUT':
				$this->updateUser($request);
				return;
				break;

			case 'DELETE':
				$this->deleteUser($request);
				return;
				break;
		}

		$resp = new SimpleResponse();
		$resp->setDataAndSend(array(), "invalid method", $resp::HTTP_BAD_REQUEST);
	}

	 
	public function getUserByName(Request $request)
	{
			
		//<luapi-gen id="validation-getUserByName">
		$validator = new getUserByNameOAS3Validator($request);
		$validationResult = $validator->validateRequest();
		if ($validationResult->validationSuccess == false) {
			$resp = new SimpleResponse();
			$resp->setDataAndSend(array(), $validationResult->errorMessage, $resp::HTTP_BAD_REQUEST);
			return;
		}
		//</luapi-gen>
	}
		
 
	public function updateUser(Request $request)
	{
			
		//<luapi-gen id="validation-updateUser">
		$validator = new updateUserOAS3Validator($request);
		$validationResult = $validator->validateRequest();
		if ($validationResult->validationSuccess == false) {
			$resp = new SimpleResponse();
			$resp->setDataAndSend(array(), $validationResult->errorMessage, $resp::HTTP_BAD_REQUEST);
			return;
		}
		//</luapi-gen>
	}
		
 
	public function deleteUser(Request $request)
	{
			
		//<luapi-gen id="validation-deleteUser">
		$validator = new deleteUserOAS3Validator($request);
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
class getUserByNameOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('username', 'path', true, false, false, '{"type":"string"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter username in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
class updateUserOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('username', 'path', true, false, false, '{"type":"string"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter username in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
class deleteUserOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('username', 'path', true, false, false, '{"type":"string"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter username in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
//</luapi-gen>
