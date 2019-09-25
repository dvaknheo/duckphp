<?php

require_once('bootstrap.php');
class Supporter extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        echo "start create Report at " .DATE(DATE_ATOM);
        //$this->createTests();
        $this->createReport();
        
        $this->assertTrue(true);
    }
    public function createReport()
    {
        MyCodeCoverage::G()->createReport();
    }
    public function createTests()
    {
        //*
        $dest=__DIR__;
        $source=__DIR__.'/../src';

        TestFileGenerator::Run($source,$dest);
        //*/
        return;
    }
}