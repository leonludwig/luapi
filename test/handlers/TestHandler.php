<?php
require '../vendor/autoload.php';

use LUAPI\Handler;
use LUAPI\Request;
use LUAPI\Response;

class TestHandler extends Handler{
    public function handle(Request $request){
        $resp = new Response();
        $resp->setData(array(
            "hello" => $request->pathParameters["variable"]
        ));
        $resp->send();
    }
}
?>