<?php
return [
'redis_list'=>
    [[
        'host'=>'redis',
        'port'=>'6379',
        'auth'=>'123456',
        'select'=>'2',
    ]],

'database_list' =>
    [[
        'dsn'=>"mysql:host=mysql;port=3306;dbname=duckphptest2;charset=utf8;",
        'username'=>'root',	
        'password'=>'123456'
    ],
    [
        'dsn'=>"mysql:host=mysql;port=3306;dbname=duckphptest2;charset=utf8;",
        'username'=>'root',	
        'password'=>'123456'
    ]],
    'options_test'=>
    [
    ],
];