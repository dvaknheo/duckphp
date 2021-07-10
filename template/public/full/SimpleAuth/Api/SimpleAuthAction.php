<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Api;

use DuckPhp\SingletonEx\SingletonExTrait;

class SimpleAuthAction
{
    use SingletonExTrait;
    public  function getCurrentUser()
    {
        return SessionManager::G()->getCurrentUser();
    }
    public function login($form)
    {
        //return 
    }
    public function logout()
    {
        //
    }
}