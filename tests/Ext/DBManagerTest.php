<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\DBManager;
use DuckPhp\DB\DB;
use DuckPhp\DuckPhp as App;

class DBManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBManager::class);
        $dn_options=[
            'skip_setting_file'=>true,
            'database_list'=>['zzz'],
        ];
        App::G()->init($dn_options);
        $database_list= include \MyCodeCoverage::G()->options['path_data'] . 'database_list.php';
        
        $options=[
        'database_list'=>$database_list,
        ];
        DBManager::G()->init($options,App::G());
        DBManager::G(new DBManager());
        
        $options=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        'db_before_get_object_handler'=>[null,'beforeGet'],
        
        'database_list'=> $database_list,
        ];
        

        App::G()->extendComponents(static::class,['beforeGet'],[]);
        DBManager::G()->init($options,App::G());
        $options['database_list']=$database_list;
        DBManager::G()->init($options,null);
        
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],[DB::class,'CloseDBInstance'],function(){echo "Exception!";});
        DBManager::G()->setBeforeGetDBHandler(function(){var_dump("OK");});

        DBManager::G()->getDBHandler();
        DBManager::G()->_Db();
        DBManager::G()->_DbForWrite();
        DBManager::G()->_DbForRead();
        DBManager::CloseAllDB();
        
        DBManager::OnException();
        
        //----------------
        $database_sinlge=[$database_list[0]];
        $options=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        
        'database_list'=>$database_sinlge,
        ];
        DBManager::G(new DBManager())->init($options);
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null,function(){echo "Exception!";});
$options['database_list']=[
];
        DBManager::G()->init($options,null);

        DBManager::Db();
        DBManager::DbForWrite();
        DBManager::DbForRead();
        DBManager::OnException();
        DBManager::G()->init($options,null);

        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null);

                DBManager::CloseAllDB();
        DBManager::OnException();

        $options=[
            'db_create_handler'=>null,
            'db_close_handler'=>null,
            'db_excption_handler'=>null,
            'db_before_get_object_handler'=>null,
            ];
    
        $options['database_list']=$database_list;
        
        DBManager::G()->init($options,null);
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null,[static::class,'onExceptions' ]);
        DBManager::G()->_Db();
        
        DBManager::OnException();
        ////
        $dn_options=[
            'skip_setting_file'=>true,
            'log_sql'=>true,
        ];
        App::G(new App())->init($dn_options);
        var_dump(App::G());
        $options=[
            'db_create_handler'=>null,
            'db_close_handler'=>null,
            'db_excption_handler'=>null,
            
            'database_list'=>$database_list,
        ];
        DBManager::G(new DBManager())->init($options,App::G());
        $data=App::DB()->fetchColumn('select ?+? as t',1,2);
        
        var_dump($data);

        //
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzz";
        $dn_options=[
            'skip_setting_file'=>true,
        ];
        DBManager::G(new DBManager());
        App::G(new App())->init($dn_options);

        try{
            App::DB();
        }catch(\Exception $ex){
        }
        try{
            App::DB('zxvf');
        }catch(\Exception $ex){
        }
        
        
        
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
