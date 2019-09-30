<?php

require_once('bootstrap.php');
class Supporter extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        
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
        echo "start create Report at " .DATE(DATE_ATOM);
        echo "\n\n";
        flush();
        $dest=__DIR__;
        $source=__DIR__.'/../src';

        TestFileGenerator::Run($source, $dest);
        //*/
        return;
    }
}
