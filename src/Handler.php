<?php
__DIR__ . '/Request.php';
__DIR__ . '/Response.php';

/**
 * an abstract class that you should extend to create your own API handler.
 */
abstract class Handler{
    /**
     * handles the given request. any return value will be ignored.
     * @param Request $request the request to handle.
     */
    abstract public function handle(Request $request);
}
?>