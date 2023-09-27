<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleActionTrait;
use DuckPhp\DuckPhp;

class SimpleActionTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleActionTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}