<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\ChangeableSingletonTrait;

class ChangeableSingletonTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ChangeableSingletonTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}