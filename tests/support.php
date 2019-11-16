<?php

require_once('bootstrap.php');
class Supporter extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        
        //$this->createTests();
        $this->createReport(); //用于创建覆盖报告 ，执行这个文件的目的
        
        $this->assertTrue(true);
    }
    public function createReport()
    {
        MyCodeCoverage::G()->createReport();
    }
    public function createTests()
    {
        //*
        echo "START CREATE REPORT AT " .DATE(DATE_ATOM);
        echo "\n\n";
        flush();
        $dest=__DIR__;
        $source=__DIR__.'/../src';

        TestFileGenerator::Run($source, $dest);
        //*/
        return;
    }
}
