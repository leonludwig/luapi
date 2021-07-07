<?php
require __DIR__ . '__DIR_UPS_TO_BASEPATH__vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\SimpleResponse;
use LUAPI\OAS3\OAS3Validator;
use LUAPI\OAS3\OAS3ValidationResult;

class StoreOrderHandler extends Handler
{
	public function handle(Request $request)
	{
		switch ($request->getMethod()) {

			case 'GET':
				$this->getOrderById($request);
				return;
				break;

			case 'DELETE':
				$this->deleteOrder($request);
				return;
				break;
		}

		$resp = new SimpleResponse();
		$resp->setDataAndSend(array(), "invalid method", $resp::HTTP_BAD_REQUEST);
	}

	 
	public function getOrderById(Request $request)
	{
			
		//<luapi-gen id="validation-getOrderById">
		$validator = new getOrderByIdOAS3Validator($request);
		$validationResult = $validator->validateRequest();
		if ($validationResult->validationSuccess == false) {
			$resp = new SimpleResponse();
			$resp->setDataAndSend(array(), $validationResult->errorMessage, $resp::HTTP_BAD_REQUEST);
			return;
		}
		//</luapi-gen>
	}
		
 
	public function deleteOrder(Request $request)
	{
			
		//<luapi-gen id="validation-deleteOrder">
		$validator = new deleteOrderOAS3Validator($request);
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
class getOrderByIdOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('orderId', 'path', true, false, false, '{"maximum":10,"minimum":1,"type":"integer","format":"int64"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter orderId in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
class deleteOrderOAS3Validator extends OAS3Validator
{
	public function validateRequest():OAS3ValidationResult
	{
		$result = $this->validateParameter('orderId', 'path', true, false, false, '{"minimum":1,"type":"integer","format":"int64"}');
		if ($result == false) {
			return new OAS3ValidationResult(false, "parameter orderId in path does not match expected schema!");
		}
		return new OAS3ValidationResult(true, "");
	}
}
//</luapi-gen>
