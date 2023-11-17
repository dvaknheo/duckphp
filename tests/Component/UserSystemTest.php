<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\UserSystem;

class UserSystemTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(UserSystem::class);
        try{
            UserSystem::_();
        } catch(\Throwable $ex){}
        UserSystem::_(MyUserSystem::_())->id();
        UserSystem::_(MyUserSystem::_())->data();
        UserSystem::_()->logoutUrl('');
        UserSystem::_()->nick();
        UserSystem::_()->username();
        \LibCoverage\LibCoverage::End();
    }
}
class MyUserSystem extends UserSystem
{
    public function __construct(){}
}
