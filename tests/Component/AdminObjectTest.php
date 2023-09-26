<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\AdminObject;

class AdminObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminObject::class);
        try{
            AdminObject::G();
        } catch(\Throwable $ex){}
        AdminObject::G(MyAdminObject::G())->id();
        AdminObject::G()->isSuper();
        AdminObject::G()->data();
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdminObject extends AdminObject
{
    public function __construct(){}
}
