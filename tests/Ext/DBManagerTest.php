<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\DBManager;

class DBManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBManager::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(DBManager::class);
        $this->assertTrue(true);
        /*
        DBManager::G()->init($options=[], $context=null);
        DBManager::G()->initContext($options=[], $context=null);
        DBManager::G()->setDBHandler($db_create_handler, $db_close_handler=null, $db_excption_handler=null);
        DBManager::G()->setBeforeGetDBHandler($before_get_db_handler);
        DBManager::G()->getDBHandler();
        DBManager::G()->_DB($tag=null);
        DBManager::G()->_DB_W();
        DBManager::G()->_DB_R();
        DBManager::G()->CloseAllDB();
        DBManager::G()->_closeAllDB();
        DBManager::G()->OnException();
        DBManager::G()->_onException();
        //*/
    }
}
