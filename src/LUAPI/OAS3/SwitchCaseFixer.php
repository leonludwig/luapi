<?php
class PHPSwitchIndentationFixer{
    public string $documentContent;

    public function __construct($documentPath)
    {
        try{
            $documentContent = file_get_contents($documentPath);
        } catch(Throwable $th){
            $documentContent = "";
        }
    }

    public function fixSwitches(string $baseIndentation = "\t\t"){
        $oldDocumentContent = $this->documentContent;
        $newDocumentContent = $oldDocumentContent;

        while(str_contains($oldDocumentContent,"switch (")){
            $startIndex = strpos($oldDocumentContent,"switch (");
            $endIndex = strpos($oldDocumentContent,"}",$startIndex+1);

            $oldSwitch = substr($oldDocumentContent,$startIndex,$endIndex+1-$startIndex);
        }
    }

    private function fixSwitch(string $switch):string{
        $switch = str_replace("\t","",$switch);
        $switchLines = explode("\n", $switch);

        $newSwitch = "";
        $newSwitchLines = array();

        foreach($switchLines as $line){
            
        }
    }
}
?>