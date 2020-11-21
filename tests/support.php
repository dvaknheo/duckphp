<?php

require_once('bootstrap.php');
class support extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        MyCodeCoverage::G()->showAllReport();//用于创建覆盖报告 ，执行这个文件的目的
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
        flush();
        MyCodeCoverage::G()->createTestFileTemplate();
        //*/
        return;
    }
}
