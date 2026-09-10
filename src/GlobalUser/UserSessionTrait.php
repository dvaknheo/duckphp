<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalUser;

trait UserSessionTrait
{
    public function getCurrentUserId()
    {
        $user = $this->get('user', []);
        return $user['id'] ?? 0;
    }
    public function getCurrentUserName(): string
    {
        $user = $this->get('user', []);
        return $user['name'] ?? '';
    }

    public function getCurrentUser(): array
    {
        return $this->get('user', []);
    }
    public function setCurrentUser($user)
    {
        $this->set('user', $user);
    }

    public function unsetCurrentUser()
    {
        $this->set('user', []);
    }
}
