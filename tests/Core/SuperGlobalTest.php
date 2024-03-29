<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\App;

class SuperGlobalTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SuperGlobal::class);
        
        SuperGlobal::_()->_SessionSet('x',DATE('Y,M,d'));
        SuperGlobal::_()->_SessionGet('x');
        SuperGlobal::_()->_SessionUnset('x');

        SuperGlobal::DefineSuperGlobalContext();
        SuperGlobal::DefineSuperGlobalContext();
        SuperGlobal::LoadSuperGlobalAll();
        SuperGlobal::SaveSuperGlobalAll();
        SuperGlobal::LoadSuperGlobal('_SERVER');
        SuperGlobal::SaveSuperGlobal('_SERVER');
        SuperGlobal::_()->_SERVER;
        
        SuperGlobal::_()->init([
            'superglobal_auto_extend_method' => true,
            'superglobal_auto_define' => true,
        ],App::_());
        
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
