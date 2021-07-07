<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class PetUploadImageHandler extends Handler
{
	public function handle(Request $request)
	{
		switch ($request->getMethod()) {

			case 'POST':
				$this->uploadFile($request);
				return;
				break;
		}

		$resp = new SimpleResponse();
		$resp->setDataAndSend(array(), "invalid method", $resp::HTTP_BAD_REQUEST);
	}

	 
	public function uploadFile(Request $request)
	{
			
		//<luapi-gen id="validation-uploadFile">
		$validator = new uploadFileOAS3Validator($request);
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
class uploadFileOAS3Validator extends OAS3Validator
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
//</luapi-gen>
