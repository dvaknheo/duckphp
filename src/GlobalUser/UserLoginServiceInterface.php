<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalUser;

interface UserLoginServiceInterface
{
    public function register(array $post);
    public function login(array $post);
    public function logout();

}
