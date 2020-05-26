<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\DBManager;
use DuckPhp\DB\DB;
use DuckPhp\App;

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
        
        $options=[
        'database_list'=>[[
                'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
                'username'=>'admin',	
                'password'=>'123456'
            ],],
        ];
        DBManager::G()->init($options,App::G());
        DBManager::G(new DBManager());
        
        $options=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        'db_before_get_object_handler'=>[null,'beforeGet'],
        
        'database_list'=>[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
],
[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]
],
        ];
        

        App::G()->extendComponents(static::class,['beforeGet'],[]);
        DBManager::G()->init($options,App::G());
        $options['database_list']=[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
],
[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]
]
;
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
        
        'database_list'=>[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]],
        ];
        DBManager::G(new DBManager())->init($options);
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null,function(){echo "Exception!";});
$options['database_list']=[
];
        DBManager::G()->init($options,null);

        DBManager::DB();
        DBManager::DB_W();
        DBManager::DB_R();
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
        
        'database_list'=>[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]],
        ];    
$options['database_list']=[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
],
[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]
]
;
        DBManager::G()->init($options,null);
        DBManager::G()->setDBHandler([DB::class,'CreateDBInstance'],null,[static::class,'onExceptions' ]);
DBManager::G()->_DB();
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
        
        'database_list'=>[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]],
        ];
        DBManager::G(new DBManager())->init($options,App::G());
        $data=App::DB()->fetchColumn('select ?+? as t',1,2);
        
        var_dump($data);

        DBManager::G()->isInited();

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
