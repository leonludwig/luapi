<?php
/**
 * NOTE: The basic structure of this handler was created by the luapi OAS3 API code generator
 * If you want to keep using the features provided by luapi code generator make sure to carefully read the following instructions
 *
 * 1.   when updating this handler using the code generator, the generator will only change the sections that start with
 *      "//<luapi-gen" and end with "//</luapi-gen>". The content will be changed to match the current content of the oas3
 *      definition file. SO: All changes you make inside these sections WILL be overwritten the next time you call update. Normally,
 *      there shouldnt be a case where you have to make changes inside the generated sections but if you have to do so and
 *      dont want your changes to be overwritten just remove the tags mentioned above and the generator will ignore these sections.
 *
 * 2.   the code-generator is a very basic class and is not able to consider custom parameters you have added to the generated
 *      functions. SO: If possible, try to avoid adding custom parameters to the auto-generated functions. Otherwise you
 *      will have to manually change the references every time you call update.
 *
 * 3.   the code generator will not remove methos from the code that arent mentioned in the definition anymore (or have been renamed)
 *
 * 4.   the code generator will not delete handlers if you remove a complete path from the definition
 */

//<luapi-gen id="require-autoload">
require __DIR__ . '../../vendor/autoload.php';
//</luapi-gen>

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
		//<luapi-gen id="switch-methods">
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
		//</luapi-gen>

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
