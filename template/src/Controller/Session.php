<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Controller;

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
