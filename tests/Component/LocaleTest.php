<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Locale;
use DuckPhp\Component\RedisManager;

class LocaleTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Locale::class);
        //
        \LibCoverage\LibCoverage::End();
    }
}