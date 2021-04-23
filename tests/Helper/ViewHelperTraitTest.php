<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\ViewHelper;
use DuckPhp\Helper\ViewHelperTrait;

class ViewHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ViewHelperTrait::class);
        
        \LibCoverage\LibCoverage::End();
    }
}
