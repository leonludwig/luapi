<?php
/**
 * cs-fixer config for code generator. you can change the file if you need to.
 */
$cfg = new PHpCsFixer\Config();
$cfg->setRules([
    '@PSR2' => true,
    'indentation_type' => true,
]);
$cfg->setIndent("\t");
$cfg->setLineEnding("\n");
return $cfg;
?>