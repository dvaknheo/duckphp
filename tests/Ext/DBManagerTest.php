<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\DBManager;
use DNMVCS\DB\DB;

class DBManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBManager::class);
        
        $options=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        'before_get_db_handler'=>null,
        
        'database_list'=>[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]],
        'use_context_db_setting'=>true,
        ];
        DBManager::G()->init($options);
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],[DB::class,'CloseDBInstance'],function(){echo "Exception!";});
        DBManager::G()->setBeforeGetDBHandler(function(){ echo "GETDB!";});
        DBManager::G()->getDBHandler();
        DBManager::G()->_DB();
        DBManager::G()->_DB_W();
        DBManager::G()->_DB_R();
        DBManager::CloseAllDB();
        DBManager::OnException();
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
