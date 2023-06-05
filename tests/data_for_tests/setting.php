<?php
return [
'redis_list'=>
    [[
        'host'=>'127.0.0.1',
        'port'=>'6379',
        'auth'=>'password1',
        'select'=>'2',
    ]],

'database_list' =>
    [[
        'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=duckphptest;charset=utf8;",
        'username'=>'user1',	
        'password'=>'123456'
    ],
    [
        'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=duckphptest;charset=utf8;",
        'username'=>'user1',	
        'password'=>'123456'
    ]],
    'options_test'=>
    [
    ],
];