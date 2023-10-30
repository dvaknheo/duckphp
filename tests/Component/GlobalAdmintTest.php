<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\GlobalAdmin;

class GlobalAdminTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalAdmin::class);
        try{
            GlobalAdmin::_();
        } catch(\Throwable $ex){}
        GlobalAdmin::_(MyGlobalAdmin::_())->id();
        GlobalAdmin::_()->isSuper();
        GlobalAdmin::_()->data();
        GlobalAdmin::_()->logoutUrl('');
        GlobalAdmin::_()->nick();
        GlobalAdmin::_()->username();
        \LibCoverage\LibCoverage::End();
    }
}
class MyGlobalAdmin extends GlobalAdmin
{
    public function __construct(){}
}
