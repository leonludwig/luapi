<?php
$cfg = new PHpCsFixer\Config();
$cfg->setRules([
    '@PSR2' => true,
    'indentation_type' => true,
]);
$cfg->setIndent("\t");
$cfg->setLineEnding("\n");
return $cfg;
?>