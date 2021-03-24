<?php
require 'src/API.php';

/*
$route = new APIRoute("/test/{var1}/test2/{var2}/{var3}");
$res = $route->matchURI($_SERVER["REQUEST_URI"]);
die(var_dump($res));
*/

$myAPI = new API();
$myAPI->addHandler("/home/test",New Handler());

$myAPI->handleRequest();


$schemaJson = <<<'JSON'
{
    "type": "object",
    "properties": {
        "id": {
            "type": "integer"
        },
        "name": {
            "type": "string"
        },
        "orders": {
            "type": "array",
            "items": {
                "$ref": "#/definitions/order"
            }
        }
    },
    "required":["id"],
    "definitions": {
        "order": {
            "type": "object",
            "properties": {
                "id": {
                    "type": "integer"
                },
                "price": {
                    "type": "number"
                },
                "updated": {
                    "type": "string",
                    "format": "date-time"
                }
            },
            "required":["id"]
        }
    }
}
JSON;
$schema = $req->createSchema($schemaJson);
$res = $req->bodyMatchesSchema($schema);
echo(var_dump($res));
?>