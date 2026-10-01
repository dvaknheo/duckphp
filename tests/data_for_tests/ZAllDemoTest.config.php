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
        // 注意：URL 一律带 ?lang=en。demo 现在是多语言的（默认 en，zh_CN 由 ?lang=/
        // cookie/Accept-Language 检测），把语言钉死在 URL 上，期望长度才不会随
        // 跑测机器的 LANG / Accept-Language 抖动。
        'test/done?lang=en'          => 95,
        'doc.php?lang=en'            => 1329,
        '?lang=en'                   => 1444,
        // files 页含「已加载文件清单 + 调用栈」，长度随类文件路径变化，每次动类文件/目录都要重算：
        //   master 的类移动（SessionTrait/ModelTrait/ExceptionReporterTrait 分目录）→ 10532
        //   Helper trait 并进 Foundation\Controller\Helper → 10531（作者后调为 10537）
        //   层 Helper 类改名为 Foundation\<层>\<层>Helper（路径变长）→ 10567
        //   默认 Admin/User 类落地 + GlobalAdmin/GlobalUser 去掉自带常量/选项（选项表与文件清单都变）→ 10438
        //   demo 的 ExceptionReporter 随 skeleton 改名 ExceptionAction（选项表出现两次 + 文件路径一处，各短 2 字节）→ 10432
        //   Logger 改成 EXT_DEFAULT 装配（修「Logger 选项不生效」）→ 长度不变（选项表只数根应用自己声明的键，
        //   Logger 只出现在「全部单例」与「包含文件」清单里，两处都没变）
        //   PhaseContainer 的 public 术语改名 shared（dump 里 `publics:` → `shared:` 少 1 字节；
        //   `#public` → `#shared`、`* is public` → `* is shared` 等长）→ 10431
        //   demo 多语言化（App 多一个选项 `lang_default` 的行、被加载的
        //   demo/config/lang-en.php 进「包含文件」清单）→ 10481
        // 依据：测试失败时自己 dump 的 tests/data_for_tests/ZAllDemoTest-<len>.txt。
        'files?lang=en'              => 10481,
        'demo.php?lang=en'           => 406,
        'helloworld.php?lang=en'     => 11,
        'just-route.php?lang=en'     => 109,
        'api.php/test.index?lang=en' => 347,
        'traditional.php?lang=en'    => 397,
        'rpc.php?lang=en'            => 129,
    ],
];
