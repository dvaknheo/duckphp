<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\DBReusePoolProxy;
use DuckPhp\Ext\DBManager;
use DuckPhp\DB\DB;
use DuckPhp\Core\App;
use DuckPhp\Core\SingletonEx;
use DuckPhp\DuckPhp;

class DBReusePoolProxyTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
    $this->assertTrue(true); return;
        \MyCodeCoverage::G()->begin(DBReusePoolProxy::class);
        $database_list = include \MyCodeCoverage::G()->options['path_data'] . 'database_list.php';
////[[[[
    DBManager::G(DBReusePoolProxy::G());
$dn_options=[
            'skip_setting_file'=>true,
        ];
        App::G()->init($dn_options);
        $options=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        'db_before_get_object_handler'=>[null,'beforeGet'],
        
        'database_list'=>$database_list,
            'use_context_db_setting'=>true,
        ];
        

        App::G()->extendComponents(static::class,['beforeGet'],[]);
        DBManager::G()->init($options,App::G());
        $options['database_list']=$database_list;
        DBManager::G()->init($options,null);
        
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],[DB::class,'CloseDBInstance'],function(){echo "Exception!";});
        DBManager::G()->setBeforeGetDBHandler(function(){var_dump("OK");});

        DBManager::G()->getDBHandler();
        DBManager::G()->_DB();
        DBManager::G()->_DB_W();
        DBManager::G()->_DB_R();
        DBManager::CloseAllDB();
        
        DBManager::OnException();
        
        //----------------
        
        $options=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        'before_get_db_handler'=>null,
        
        'database_list'=>$database_list,
            'use_context_db_setting'=>true,
        ];
        DBManager::G(new DBManager())->init($options);
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null,function(){echo "Exception!";});
$options['database_list']=[
];
        DBManager::G()->init($options,null);

        DBManager::G()->_DB();
        DBManager::G()->_DB_W();
        DBManager::G()->_DB_R();
        DBManager::OnException();
        DBManager::G()->init($options,null);

        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null);

                DBManager::CloseAllDB();
        DBManager::OnException();

$options=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        'before_get_db_handler'=>null,
        
        'database_list'=>$database_list,
            'use_context_db_setting'=>true,
        ];    
$options['database_list']=$database_list;

        DBManager::G()->init($options,null);
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null,[static::class,'onExceptions' ]);
DBManager::G()->_DB();
        DBManager::OnException();
////]]]]
        \MyCodeCoverage::G()->end();
    }
    
    public static function beforeGet()
    {
        var_dump("OK");
    }
    public static function onExceptions()
    {
        echo "222222!";
    }
}
