<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\UserObject;

class UserObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(UserObject::class);
        try{
            UserObject::G();
        } catch(\Throwable $ex){}
        UserObject::G(MyUserObject::G())->id();
        UserObject::G(MyUserObject::G())->data();
        \LibCoverage\LibCoverage::End();
    }
}
class MyUserObject extends UserObject
{
    public function __construct(){}
}
