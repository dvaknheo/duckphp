<?php declare(strict_types=1);
/**
 * All session in here
 * Do not call other class.
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\SessionTrait;

class Session
{
    use SessionTrait;
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
