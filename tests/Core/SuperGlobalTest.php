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
        
        App::GET('a');
        App::POST('a');
        App::REQUEST('a');
        App::COOKIE('a');
        App::SERVER('SCRIPT_FILENAME');
        
        App::GET();
        App::POST();
        App::REQUEST();
        App::COOKIE();
        App::SERVER();
        App::SESSION();
        App::FILES();
        
        App::Route();
        
        SuperGlobal::DefineSuperGlobalContext();
        App::SessionSet('x',DATE('Y,M,d'));
        App::SessionUnset('x');

        App::CookieSet('x',DATE('Y,M,d'));
        App::CookieGet('x');
        
        
        
        
        \LibCoverage\LibCoverage::End();
    }
}
