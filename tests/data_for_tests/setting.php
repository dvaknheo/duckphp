<?php
$full_db_file = __DIR__.'/dbtest.sqlite';
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
        'dsn'=>"sqlite:$full_db_file",
        'username'=>'root',	
        'password'=>'123456'
    ],
    [
        'dsn'=>"sqlite:$full_db_file",
        'username'=>'root',	
        'password'=>'123456'
    ]],
    'options_test'=>
    [
    ],
];