<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Locale;
use DuckPhp\Component\RedisManager;
use DuckPhp\DuckPhp;

class LocaleTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Locale::class);
		
		DuckPhp::_()->init([
            'is_debug'=>true,
        ]);
        __l("Hello");
		
		
        \LibCoverage\LibCoverage::End();
    }
}