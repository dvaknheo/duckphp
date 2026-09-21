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
        // files 页含「已加载文件清单 + 调用栈」，其长度随类文件路径变化：
        //   master 的类移动（SessionTrait/ModelTrait/ExceptionReporterTrait 分目录）→ 10532
        //   本次 Helper trait 并进 Foundation\Controller\Helper（路径短 1 字节）→ 10531
        // 依据：tests/data_for_tests/ZAllDemoTest-10531.txt（测试自己在失败时 dump）。详见 helper-merge-checklist.md
        'files'              => 10531,
        'demo.php'           => 406,
        'helloworld.php'     => 11,
        'just-route.php'     => 109,
        'api.php/test.index' => 347,
        'traditional.php'    => 397,
        'rpc.php'            => 129,
    ],
];
