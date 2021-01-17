<?php
return [
'redis_list'=>
    [[
        'host'=>'??????',
        'port'=>'6379',
        'auth'=>'??????',
        'select'=>'2',
    ]],

'database_list' =>
    [[
        'dsn'=>"mysql:host=????;port=3306;dbname=????;charset=utf8;",
        'username'=>'??????',	
        'password'=>'??????'
    ],
    [
        'dsn'=>"mysql:host=????;port=3306;dbname=????;charset=utf8;",
        'username'=>'??????',	
        'password'=>'??????'
    ]],
    'options_test'=>
    [
        //'path' => null,
        //'namespace' => null,
        //'auto_detect_namespace' => true,
        //'path_src' => 'src',
        //'path_dump' => 'test_coveragedumps',
        //'path_report' => 'test_reports',
        //'path_data' => 'tests/data_for_tests',
    ],
];