<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\SessionBase;

class SessionTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SessionBase::class);
        SessionManager::G()->setCurrentUser(['id'=>'1','name'=>'dx']);
        SessionManager::G()->getCurrentUser();
        SessionManager::G()->logoutUser();
        
        \LibCoverage\LibCoverage::End();
    }
}
class SessionManager extends SessionBase
{
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
