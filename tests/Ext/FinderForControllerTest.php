<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\FinderForController;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;

class FinderForControllerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(FinderForController::class);
        \LibCoverage\LibCoverage::End();
    }
}