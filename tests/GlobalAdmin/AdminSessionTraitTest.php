<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\GlobalAdmin\AdminSessionTrait;
use DuckPhp\Foundation\SessionTrait;

class AdminSessionTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminSessionTrait::class);

        $obj = new MyAdminSessionTrait();

        // Test setCurrentAdmin and getCurrentAdminId
        $obj->setCurrentAdmin(['id' => 123, 'name' => 'admin_test']);
        \PHPUnit\Framework\Assert::assertEquals(123, $obj->getCurrentAdminId());
        \PHPUnit\Framework\Assert::assertEquals('admin_test', $obj->getCurrentAdminName());
        \PHPUnit\Framework\Assert::assertEquals(['id' => 123, 'name' => 'admin_test'], $obj->getCurrentAdmin());

        // Test unsetCurrentAdmin
        $obj->unsetCurrentAdmin();
        \PHPUnit\Framework\Assert::assertEquals(0, $obj->getCurrentAdminId());
        \PHPUnit\Framework\Assert::assertEquals('', $obj->getCurrentAdminName());
        \PHPUnit\Framework\Assert::assertEquals([], $obj->getCurrentAdmin());

        // Test empty admin
        \PHPUnit\Framework\Assert::assertEquals(0, $obj->getCurrentAdminId());
        \PHPUnit\Framework\Assert::assertEquals('', $obj->getCurrentAdminName());

        \LibCoverage\LibCoverage::End();
    }
}

// Mock class that uses SessionTrait to provide get()/set() methods
class MyAdminSessionTrait
{
    use SessionTrait;
    use AdminSessionTrait;
}
