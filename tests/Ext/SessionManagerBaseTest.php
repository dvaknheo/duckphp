<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\SessionManagerBase;

class SessionManagerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SessionManagerBase::class);
        SessionManager::G()->setCurrentUser(['id'=>'1','name'=>'dx']);
        SessionManager::G()->getCurrentUser();
        SessionManager::G()->logoutUser();
        
        \LibCoverage\LibCoverage::End();
    }
}
class SessionManager extends SessionManagerBase
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
