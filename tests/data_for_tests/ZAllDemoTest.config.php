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
        // files 页含「已加载文件清单 + 调用栈」，长度随类文件路径变化，每次动类文件/目录都要重算：
        //   master 的类移动（SessionTrait/ModelTrait/ExceptionReporterTrait 分目录）→ 10532
        //   Helper trait 并进 Foundation\Controller\Helper → 10531（作者后调为 10537）
        //   层 Helper 类改名为 Foundation\<层>\<层>Helper（路径变长）→ 10567
        // 依据：测试失败时自己 dump 的 tests/data_for_tests/ZAllDemoTest-<len>.txt。详见 helper-merge-checklist.md
        'files'              => 10567,
        'demo.php'           => 406,
        'helloworld.php'     => 11,
        'just-route.php'     => 109,
        'api.php/test.index' => 347,
        'traditional.php'    => 397,
        'rpc.php'            => 129,
    ],
];
