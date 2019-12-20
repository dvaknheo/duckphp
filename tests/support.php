<?php

require_once('bootstrap.php');
class Supporter extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        
        //$this->_testCreateTests();
        $this->createReport(); //用于创建覆盖报告 ，执行这个文件的目的
        
        $this->assertTrue(true);
    }
    public function createReport()
    {
        echo "\nSTART CREATE REPORT AT " .DATE(DATE_ATOM)."\n";
        echo "File: file://".__DIR__."/test_reports/index.html" ."\n";
        echo "\n\n";
        echo "\n\n";        
        MyCodeCoverage::G()->createReport();
    }
    public function _testCreateTests()
    {
        //*
        echo "START CREATE template AT " .DATE(DATE_ATOM);
        echo "\n\n";
        flush();
        $dest=__DIR__;
        $source=__DIR__.'/../src';

        TestFileGenerator::Run($source, $dest);
        //*/
        return;
    }
}
