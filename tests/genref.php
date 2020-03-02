<?php
require_once('bootstrap.php');
class GenRefer extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        //生成文档
        
        RefFileGenerator::Run();
        
        $this->assertTrue(true);
    }

}
