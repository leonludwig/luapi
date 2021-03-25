<?php
require '../vendor/autoload.php';
use LUAPI\API;
use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;

$myAPI = new API();

class MyCustomHandler extends Handler{
    public function handle(Request $request){
        $resp = new Response();
        $resp->setData(array(
            "hello" => "world!"
        ));
        $resp->send();
    }
}

$myAPI->addHandler("/home/test",New MyCustomHandler());

$myAPI->handleRequest();
?>