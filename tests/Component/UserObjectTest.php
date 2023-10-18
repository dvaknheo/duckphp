<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\UserObject;

class UserObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(UserObject::class);
        try{
            UserObject::_();
        } catch(\Throwable $ex){}
        UserObject::_(MyUserObject::_())->id();
        UserObject::_(MyUserObject::_())->data();
        UserObject::_()->logoutUrl('');
        UserObject::_()->nick();
        UserObject::_()->username();
        \LibCoverage\LibCoverage::End();
    }
}
class MyUserObject extends UserObject
{
    public function __construct(){}
}
