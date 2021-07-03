<?php
function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}
if ((!$loader = includeIfExists(__DIR__.'/../../../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../../../autoload.php'))) {
    fwrite(STDERR,
        'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL
    );
    exit(1);
}

use LUAPI\OAS3\APICodeGenerator;

$targetDirectory = $argv[1];
$definitionFileName = "";
if(file_exists($targetDirectory . "/oas3_definition.yaml")){
    $definitionFileName = $targetDirectory . "/oas3_definition.yaml";
}
if(file_exists($targetDirectory . "/oas3_definition.json")){
    $definitionFileName = $targetDirectory . "/oas3_definition.json";
}
if($definitionFileName == ""){
    print("Definition file oas3_definition.[json/yaml] not found!");
    exit();
}

$generator = null;
try{
    $generator = new APICodeGenerator($definitionFileName);
} catch (Throwable $th){
    print("invalid OAS3 definition.");
    print($th->getMessage());
    exit();
}

try{
    $generator->buildAPI($targetDirectory);
} catch (Throwable $th){
    print("failed to build API.\r\n");
    print("Error MSG:" . $th->getMessage() . "\r\n");
    print("Line:" . $th->getLine() . "\r\n");
    exit();
}

try{
    exec("vendor/bin/php-cs-fixer fix test/generator/handlers/ --config src/LUAPI/OAS3/csfixer-config.php");
} catch(Throwable $th){
    print("failed to run cs-fixer.\r\n");
    print("Error MSG:" . $th->getMessage() . "\r\n");
    print("Line:" . $th->getLine() . "\r\n");
    exit();
}

/**
 * 1. re-include php-cs-fixer
 * 2. fix all files in targetdir
 * 3. custom-fix switch(case)
 */
?>