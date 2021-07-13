<?php
namespace LUAPI\OAS3;

use Throwable;

/**
 * A class that can be used to fix the indentation of bad formatted switch-cases in PHP documents
 */
class PHPSwitchIndentationFixer{
    public string $documentPath;
    public string $documentContent;

    /**
     * will load the content of the document at the given path
     */
    public function __construct($documentPath = "")
    {
        if($documentPath !== ""){
            $this->documentPath = $documentPath;
            $this->documentContent = file_get_contents($documentPath);
        }
    }

    /**
     * fixes the indentation of switch cases inside the document.
     * @param string $baseIndentation the base indentation to build (target indentation before "switch")
     * @param string $indentation a single indentation step
     * @param string $newLine the newLine character to use / split lines by
     * @param string $switchIndentationPrefix the expected indentation prefix of the "switch" (usually the same as $baseIndentation)
     */
    public function fixSwitchesInDocument(string $baseIndentation = "\t\t"){
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

    /**
     * fixes the indentation of the provided switch case.
     * @param string $switch the full switch-case section (from switch(){ to })
     * @param string $baseIndentation the base indentation to build (target indentation before "switch")
     * @param string $indentation a single indentation step
     * @param string $newLine the newLine character to use / split lines by
     */
    public function getFixedSwitch(string $switch, string $baseIndentation = "\t\t"):string{
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