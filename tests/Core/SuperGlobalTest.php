<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\App;

class SuperGlobalTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SuperGlobal::class);
        
        App::SessionSet('x',DATE('Y,M,d'));
        App::SessionGet('x');
        App::SessionUnset('x');

        SuperGlobal::DefineSuperGlobalContext();
        SuperGlobal::DefineSuperGlobalContext();
        SuperGlobal::LoadSuperGlobalAll();
        SuperGlobal::SaveSuperGlobalAll();
        SuperGlobal::LoadSuperGlobal('_SERVER');
        SuperGlobal::SaveSuperGlobal('_SERVER');
        SuperGlobal::G()->_SERVER;
        
        SuperGlobal::G()->init([
            'superglobal_auto_extend_method' => true,
            'superglobal_auto_define' => true,
        ],App::G());
        
        SuperGlobal::_()->_GET('a');
        SuperGlobal::_()->_POST('a');
        SuperGlobal::_()->_REQUEST('a');
        SuperGlobal::_()->_COOKIE('a');
        SuperGlobal::_()->_SERVER('SCRIPT_FILENAME');
        
        SuperGlobal::_()->_GET();
        SuperGlobal::_()->_POST();
        SuperGlobal::_()->_REQUEST();
        SuperGlobal::_()->_COOKIE();
        SuperGlobal::_()->_SERVER();
        SuperGlobal::_()->_SESSION();
        SuperGlobal::_()->_FILES();
        
        //App::Route();
        
        SuperGlobal::DefineSuperGlobalContext();
        SuperGlobal::_()->_SessionSet('x',DATE('Y,M,d'));
        SuperGlobal::_()->_SessionUnset('x');

        SuperGlobal::_()->_CookieSet('x',DATE('Y,M,d'));
        SuperGlobal::_()->_CookieGet('x');
        
        
        
        
        \LibCoverage\LibCoverage::End();
    }
}
