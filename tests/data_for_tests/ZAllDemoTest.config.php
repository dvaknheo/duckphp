<?php
return [
    'path_app' => realpath(__DIR__.'/../../template/').'/',
    'port' => 9529,
    'server_options' => [
        'path' => realpath(__DIR__.'/../../template/').'/',
        'path_document' => 'public',
        'port' => 9529,
        'background' => true,
    ],
    'tests' => [
        'test/done'          => 95,
        'doc.php'            => 1329,
        ''                   => 1363,
        'files'              => 11693,
        'demo.php'           => 406,
        'helloworld.php'     => 11,
        'just-route.php'     => 109,
        'api.php/test.index' => 347,
        'traditional.php'    => 397,
        'rpc.php'            => 812,
    ],
];
