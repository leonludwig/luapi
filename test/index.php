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
        $schema = new JSONSchemaBuilder();
        $schema->expect("company",new SchemaString("name",3,50));
        $schema->expect("company.address",new SchemaString("street",3,50));
        $schema->expect("company.address",new SchemaNumber("houseNr",true));
        $schema->expect("responsible",new SchemaString("firstName",2,50));
        $schema->expect("responsible",new SchemaString("lastName",2,50));
        echo($schema->toJSON());


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