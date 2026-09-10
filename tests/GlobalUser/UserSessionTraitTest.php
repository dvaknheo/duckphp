<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\Foundation\Controller\UserSessionTrait;
use DuckPhp\Foundation\SessionTrait;

class UserSessionTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(UserSessionTrait::class);

        $obj = new MyUserSessionTrait();

        // Test setCurrentUser and getCurrentUserId
        $obj->setCurrentUser(['id' => 456, 'name' => 'user_test']);
        \PHPUnit\Framework\Assert::assertEquals(456, $obj->getCurrentUserId());
        \PHPUnit\Framework\Assert::assertEquals('user_test', $obj->getCurrentUserName());
        \PHPUnit\Framework\Assert::assertEquals(['id' => 456, 'name' => 'user_test'], $obj->getCurrentUser());

        // Test unsetCurrentUser
        $obj->unsetCurrentUser();
        \PHPUnit\Framework\Assert::assertEquals(0, $obj->getCurrentUserId());
        \PHPUnit\Framework\Assert::assertEquals('', $obj->getCurrentUserName());
        \PHPUnit\Framework\Assert::assertEquals([], $obj->getCurrentUser());

        // Test empty user
        \PHPUnit\Framework\Assert::assertEquals(0, $obj->getCurrentUserId());
        \PHPUnit\Framework\Assert::assertEquals('', $obj->getCurrentUserName());

        \LibCoverage\LibCoverage::End();
    }
}

// Mock class that uses SessionTrait to provide get()/set() methods
class MyUserSessionTrait
{
    use \DuckPhp\Foundation\SessionTrait;
    use \DuckPhp\Foundation\Controller\UserSessionTrait;
}
