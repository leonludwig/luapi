<?php
namespace LUAPI\Exceptions;

use Exception;

class SQLInterfaceException extends Exception {
    public function __construct(String $message = "",String $query = "", Exception $previous = null)
    {
        if($query != ""){
            $message .= " $query";
        }

        parent::__construct($message,0,$previous);
    }
}
?>