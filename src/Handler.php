<?php
require 'Request.php';
require 'Response.php';

abstract class Handler{
    abstract public function handle($request);
}
?>