<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\AdminSystem;

class AdminSystemTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminSystem::class);
        
        try{
            AdminSystem::_();
        } catch(\Throwable $ex){}
        AdminSystem::_(MyAdminSystem::_())->id();
        AdminSystem::_()->data();
        
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdminSystem extends AdminSystem
{
    public function __construct(){}
}
