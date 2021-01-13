<?php

require_once('bootstrap.php');
class support extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
$c_args=[
'--coverage-clover',
'--coverage-crap4j',
'--coverage-html',
'--coverage-php',
'--coverage-text',
];
$in_coverage=false;
foreach($c_args as $v){
    if(!in_array($v,$_SERVER['argv'])){ continue; }
    $in_coverage=true;
}
if($in_coverage){
$this->assertTrue(true);
echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz";
    return;
}

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
