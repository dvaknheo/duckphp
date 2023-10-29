<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\AdminObject;

class AdminObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminObject::class);
        try{
            AdminObject::_();
        } catch(\Throwable $ex){}
        AdminObject::_(MyAdminObject::_())->id();
        AdminObject::_()->isSuper();
        AdminObject::_()->data();
        AdminObject::_()->logoutUrl('');
        AdminObject::_()->nick();
        AdminObject::_()->username();
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdminObject extends AdminObject
{
    public function __construct(){}
}
