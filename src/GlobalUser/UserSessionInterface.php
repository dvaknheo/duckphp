<?php declare(strict_types=1);
namespace DuckAdmin\DemoUsers\System;
interface UserSessionInterface
{
    public function setCurrentUser($user);
    public function unsetCurrentUser();
    public function getCurrentUser();
    public function getCurrentUserName();
    public function getCurrentUserId();    
}