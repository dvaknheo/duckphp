<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\GlobalUser;

class GlobalUserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalUser::class);
        try{
            GlobalUser::_();
        } catch(\Throwable $ex){}
        GlobalUser::_(MyGlobalUser::_())->id();
        GlobalUser::_(MyGlobalUser::_())->data();
        GlobalUser::_()->logoutUrl('');
        GlobalUser::_()->nick();
        GlobalUser::_()->username();
        \LibCoverage\LibCoverage::End();
    }
}
class MyGlobalUser extends GlobalUser
{
    public function __construct(){}
}
