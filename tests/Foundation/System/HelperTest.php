<?php
namespace tests\DuckPhp\Foundation\System;

use DuckPhp\Foundation\System\Helper;
use PHPUnit\Framework\Assert;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);
        new Helper();
        \LibCoverage\LibCoverage::End(Helper::class);

    }

}
