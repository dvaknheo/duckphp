<?php
require_once('bootstrap.php');

class support extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        \LibCoverage\LibCoverage::G()->showAllReport();//用于创建覆盖报告 ，执行这个文件的目的
        $this->assertTrue(true);
    }
    public function createReport()
    {
        
    }
    public function _testCreateTests()
    {
        //*
        echo "START CREATE template AT " .DATE(DATE_ATOM);
        echo "\n\n";
        \LibCoverage\LibCoverage::G()->createTests();
        return;
    }
}
