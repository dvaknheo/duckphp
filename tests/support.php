<?php
require_once(__DIR__ . '/bootstrap.php');

class support extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
		echo "e.g. composer run-script fulltest\n";
		echo "e.g. composer run-script singletest tests/Core/AppTest.php\n";
        // XDEBUG_MODE=coverage 应通过环境变量设置，ini_set 在 PHP 8.4+ 对 PHP_INI_SYSTEM 无效
        if (PHP_VERSION_ID < 80000) {
            ini_set('xdebug.mode', 'coverage');
        }
        \LibCoverage\LibCoverage::G()->showAllReport(); // create all report
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
