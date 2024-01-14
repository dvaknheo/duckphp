<?php
namespace tests\DuckPhp\Foundation\Model;

use DuckPhp\Foundation\Model\Helper;
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
