# What is LUAPI?
i was looking for a simple API framework for PHP that provides basic rest API functionalities. I could not find something that fis my needs so I created LUAPI. Its my first public repo & package.

# Basic Setup 
1. go to your project, open a command-line and type:
```
composer require leonludwig/luapi
```
2. in your webroot create an "index.php" file
3. redirect all requests to this index.php file. example with apache and a .htaccess file:
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^.*$ /index.php [L,QSA]
```
5. create a handlers.json file to describe the handlers to the API.
```json
{
    "/home/test":{
        "path":"",
        "className":"MyCustomHandler"
    }
}
```
In this example, the path is not specified because the handler is in the same file and does not have to be included.
In a real-world API the "path" value should be the path to the handler PHP file.
4. go to your index.php and type:
```php
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
```
5. the basic structure should be self explanatory. All classes & functions are documented.

# Basic Setup < 0.2.0
1. go to your project, open a command-line and type:
```
composer require leonludwig/luapi
```
2. in your webroot create an "index.php" file
3. redirect all requests to this index.php file. example with apache and a .htaccess file:
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^.*$ /index.php [L,QSA]
```
4. go to your index.php and type:
```php
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
```
5. the basic structure should be self explanatory. All classes & functions are documented.

# TODO
* add a get-started guide & explanation on how LUAPI works & should be used.