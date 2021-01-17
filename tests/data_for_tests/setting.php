<?php
return [
'redis_list'=>
    [[
        'host'=>'127.0.0.1',
        'port'=>'6379',
        'auth'=>'cgbauth',
        'select'=>'2',
    ]],

'database_list' =>
    [[
        'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
        'username'=>'admin',	
        'password'=>'123456'
    ],
    [
        'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
        'username'=>'admin',	
        'password'=>'123456'
    ]],
    'options_test'=>
    [
    ],
];