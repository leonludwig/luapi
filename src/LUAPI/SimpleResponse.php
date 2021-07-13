<?php
namespace LUAPI;

/**
 * an easy class that extends the response. Youi can use it if you just want to send a fast response without much customization.
 */
class SimpleResponse extends Response{
    /**
     * sets the response data and sends it together with the status code
     * @param mixed $data the response data
     * @param string $error the error message
     * @param int $statusCode the status code
     */
    public function setDataAndSend(mixed $data, string $error, int $statusCode):void{
        $this->setData(array(
            "error" => $error,
            "data" => $data
        ));
        $this->setResponseCode($statusCode);
        $this->send();
    }
}
?>