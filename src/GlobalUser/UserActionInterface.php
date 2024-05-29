<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserActionInterface
{
    public function id();
    public function name();
    
    public function login(array $post);
    public function logout(array $post);
    public function regist(array $post);
}
