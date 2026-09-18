<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\PermissionMenu;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\SingletonExTrait as SingletonExTrait;

class PermissionMenuTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(PermissionMenu::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(PermissionMenu::class);
        
        //Testing codes

        \LibCoverage\LibCoverage::End();

    }
}