<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\ExitException;
use DuckPhp\Core\App;

class ExitExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExitException::class);
        ExitException::Init();
        ExitException::Init();
        \LibCoverage\LibCoverage::End();

    }
}
