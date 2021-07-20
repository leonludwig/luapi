<?php
require '../vendor/autoload.php';
use LUAPI\API;
use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;
use LUAPI\Validation\JSONSchemaBuilder;
use LUAPI\Validation\SchemaString;
use LUAPI\Validation\SchemaNumber;

$myAPI = new API();

class MyCustomHandler extends Handler{
    public function handle(Request $request){
        $resp = new Response();
        $resp->setData(array(
            "hello" => "test!"
        ));
        $resp->send();
    }
}

$myAPI->loadHandlers(__DIR__ . "/handlers.json");
$myAPI->setDefaultHandlerNameAndPath("MyCustomHandler","");

$myAPI->handleRequest();
?>