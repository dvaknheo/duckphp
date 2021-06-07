<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\SuperGlobalContext;
use DuckPhp\Core\App;

class SuperGlobalContextTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SuperGlobalContext::class);
        
        SuperGlobalContext::DefineSuperGlobalContext();
        SuperGlobalContext::DefineSuperGlobalContext();
        SuperGlobalContext::LoadSuperGlobalAll();
        SuperGlobalContext::SaveSuperGlobalAll();
        SuperGlobalContext::LoadSuperGlobal('_SERVER');
        SuperGlobalContext::SaveSuperGlobal('_SERVER');
        SuperGlobalContext::G()->_SERVER;
        
        SuperGlobalContext::G()->init([
            'superglobal_auto_extend_method' => true,
            'superglobal_auto_define' => true,
        ],App::G());
        \LibCoverage\LibCoverage::End();
    }
}
