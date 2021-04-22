<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\ViewHelper;

class ViewHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ViewHelper::class);
        
        \LibCoverage\LibCoverage::End();
    }
}
