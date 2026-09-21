<?php
return [
    'echo_failed_content' => false,
    'path_app' => realpath(__DIR__.'/../../demo/').'/',
    'port' => 9802,
    'server_options' => [
        'path' => realpath(__DIR__.'/../../demo/').'/',
        'path_document' => 'public',
        'port' => 9802,
        'background' => true,
        'workers' => 4,
    ],
    'tests' => [
        'test/done'          => 95,
        'doc.php'            => 1329,
        ''                   => 1363,
        'files'              => 10583,
        'demo.php'           => 406,
        'helloworld.php'     => 11,
        'just-route.php'     => 109,
        'api.php/test.index' => 347,
        'traditional.php'    => 397,
        'rpc.php'            => 129,
    ],
];
