<?php
namespace LUAPI\OAS3;

use Throwable;

class PHPSwitchIndentationFixer{
    public string $documentPath;
    public string $documentContent;

    public function __construct($documentPath)
    {
        try{
            $this->documentPath = $documentPath;
            $this->documentContent = file_get_contents($documentPath);
        } catch(Throwable $th){
            $documentContent = "";
        }
    }

    public function fixSwitches(string $baseIndentation = "\t\t"){
        $oldDocumentContent = $this->documentContent;
        $newDocumentContent = $oldDocumentContent;

        while(str_contains($oldDocumentContent,"\t\tswitch (")){
            $startIndex = strpos($oldDocumentContent,"\t\tswitch (");
            $endIndex = strpos($oldDocumentContent,"}",$startIndex+1);

            $oldSwitch = substr($oldDocumentContent,$startIndex,$endIndex+1-$startIndex);
            $newSwitch = $this->getFixedSwitch($oldSwitch,$baseIndentation);

            $newDocumentContent = str_replace($oldSwitch,$newSwitch,$newDocumentContent);
            $oldDocumentContent = str_replace($oldSwitch,"",$oldDocumentContent);
        }

        $handle = fopen($this->documentPath,"w");
        fwrite($handle,$newDocumentContent);
        fclose($handle);
    }

    private function getFixedSwitch(string $switch, string $baseIndentation):string{
        $switch = trim($switch);
        $switch = str_replace("\t","",$switch);
        $switchLines = explode("\n", $switch);

        $newSwitchLines = array();

        foreach($switchLines as $line){
            $newLine = trim($line);

            if(str_starts_with($newLine,"switch ") || str_starts_with($newLine,"}")){
                $newLine = $baseIndentation . $newLine;
            } else if(str_starts_with($newLine,"case ")){
                $newLine = "\n" . $baseIndentation . "\t" . $newLine;
            } else {
                $newLine = $baseIndentation . "\t\t" . $newLine;
            }

            

            if(trim($line) !== ""){
                array_push($newSwitchLines,$newLine);
            }
        }

        return implode("\n",$newSwitchLines);
    }
}
?>