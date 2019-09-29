<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\DBReusePoolProxy;
use DNMVCS\Core\SingletonEx;
use DNMVCS\DNMVCS;

class DBReusePoolProxyTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBReusePoolProxy::class);
        
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
        DBManager::G()->setDBHandler([DBReusePoolProxyTestObject::class,'CreateDBInstance'],[DBReusePoolProxyTestObject::class,'CloseDBInstance'],[DBReusePoolProxyTestObject::class,'OnException']);
        
        $options=[
        
        ];
        $context=DNMVCS::G();
        DBReusePoolProxy::G()->init($options=[],$context);
          
          
        \MyCodeCoverage::G()->end(DBReusePoolProxy::class);
        $this->assertTrue(true);
    }
}
class DBReusePoolProxyTestObject
{
    use SingletonEx;
    public static function CreateDBInstance($config,$tag)
    {
        $ret=new \stdClass
    }
    public static function CloseDBInstance($object,$tag)
    {
    }
    public static function OnException($object,$tag)
    {
    }
}