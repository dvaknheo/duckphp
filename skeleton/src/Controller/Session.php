<?php declare(strict_types=1);
/**
 * All session in here
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\SimpleSessionTrait;

class Session
{
    use SimpleSessionTrait;
    /*
    public function getCurrentUser()
    {
        return $this->get('user', []);
    }
    public function setCurrentUser($user)
    {
        return $this->set('user', $user);
    }
    */
}
