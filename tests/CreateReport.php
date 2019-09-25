<?php
require_once('bootstrap.php');
class CreateReport extends \PHPUnit\Framework\TestCase
{
    public function testMain()
    {
        MyCodeCoverage::G()->createReport();
        $this->assertTrue(true);
    }
}