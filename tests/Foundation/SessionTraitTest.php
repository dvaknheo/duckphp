<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SessionTrait;

class SessionTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SessionTrait::class);
        SessionManager::_()->setCurrentUser(['id'=>'1','name'=>'dx']);
        SessionManager::_()->getCurrentUser();
        SessionManager::_()->logoutUser();
        
        \LibCoverage\LibCoverage::End();
    }
}
class SessionManager
{
    use SessionTrait;
    public function setCurrentUser($user)
    {
        $this->set('user',$user);
    }
    public function getCurrentUser()
    {
        $this->get('user');
    }
    public function logoutUser()
    {
        $this->unset('user');
    }
}
