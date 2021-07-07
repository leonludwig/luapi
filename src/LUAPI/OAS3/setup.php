<?php
if(sizeof($argv) < 2){
    fwrite(STDOUT,
        'LUAPI OAS3 API Code Generator'.PHP_EOL.
    );
}


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
use LUAPI\OAS3\PHPSwitchIndentationFixer;

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
    exec("php vendor/bin/php-cs-fixer fix test/generator/handlers/ --config src/LUAPI/OAS3/csfixer-config.php");
} catch(Throwable $th){
    print("failed to run cs-fixer.\r\n");
    print("Error MSG:" . $th->getMessage() . "\r\n");
    print("Line:" . $th->getLine() . "\r\n");
    exit();
}

try{
    $allFiles = getDirContents($targetDirectory);
    foreach($allFiles as $filePath){
        if(str_ends_with($filePath,".php")){
            $fixer = new PHPSwitchIndentationFixer($filePath);
            $fixer->fixSwitches();
        }
    }
} catch(Throwable $th){
    echo($th->getTraceAsString());
    print("failed to fix Switch-Cases.\r\n");
    print("Error MSG:" . $th->getMessage() . "\r\n");
    print("Line:" . $th->getLine() . "\r\n");
    exit();
}

function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}
?>