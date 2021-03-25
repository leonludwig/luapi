#What is LUAPI?
i was looking for a simple API framework for PHP that provides basic API functionalities. I could not find something that fis my needs so I created LUAPI. Its my first public repo & package.

#Setup
1. go to your project, open a command-line and type:
    composer require leonludwig/luapi
2. in your webroot create an "index.php" file
3. redirect all requests to this index.php file. example with a .htaccess file:
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^.*$ /index.php [L,QSA]
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

#TODO
* add a get-started guide & explanation on how LUAPI works & should be used.