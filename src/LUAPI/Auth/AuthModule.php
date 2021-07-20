<?php
namespace LUAPI\Auth;

use LUAPI\Request;

/**
 * a basic class template for authentication modules.
 * all custom implemented functionalities should be static!
 */
abstract class AuthModule {
    /**
     * static function used to check the authentication of a request
     * @param Request $request 
     */
    public abstract static function authenticate(Request $request):bool;
}

?>