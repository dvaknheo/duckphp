<?php

require_once('bootstrap.php');
class support extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        
        //$this->_testCreateTests();
        $this->createReport(); //用于创建覆盖报告 ，执行这个文件的目的
        
        $this->assertTrue(true);
    }
    public function createReport()
    {
        $data = MyCodeCoverage::G()->createReport();
        echo "\nSTART CREATE REPORT AT " .DATE(DATE_ATOM)."\n";
        echo "File:\nfile://".MyCodeCoverage::G()->options['path_report']."/index.html" ."\n"; 
                echo "\n\033[42;30m All Done \033[0m Test Done!";

        echo "\nTest Lines: \033[42;30m{$data['lines_tested']}/{$data['lines_total']}({$data['lines_percent']})\033[0m\n";
        echo "\n\n";
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
