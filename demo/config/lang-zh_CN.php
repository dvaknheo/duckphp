<?php declare(strict_types=1);
/**
 * DuckPHP demo - 中文句子（与 `lang-en.php` 同一套 key）。
 *
 * key 是扁平的：点号只是名字的一部分，`Lang` 不做路径查找
 * （`'a.b'` 就是字面 key `a.b`）。
 *
 * 默认语言是 `en`（`lang_default`）；本文件在 `?lang=zh_CN`、
 * `lang` cookie 或 `Accept-Language: zh-CN` 时生效。
 *
 *   demo/view/main.php      → main.*        欢迎页
 *
 * 这里缺的 key 会回落成 key 本身（见 `lang_warn_on_missing`）。
 */
return [
    'hello'                     => '你好',

    'main.welcome'              => '欢迎使用 DuckPhp，',
    'main.see_demo'             => '查看 Demo 结果',
    'main.doc_in_app'           => 'DuckPhp 文档（框架内模式）',
    'main.doc_standalone'       => 'DuckPhp 文档（独立页面）',
    'main.examples_blurb'       => '常用例子，不需要单独配置',
    'main.link.files'           => '/files 查看示例堆栈和包含文件',
    'main.link.demo'            => 'demo.php 单一文件演示所有操作',
    'main.link.helloworld'      => 'helloworld.php 常见的 helloworld',
    'main.link.just_route'      => 'just-route.php 只要路由',
    'main.link.api'             => 'api.php 作为 api 服务器的例子，不需要控制器了',
    'main.link.traditional'     => 'traditional.php 传统模式，一个文件解决，不折腾那么多',
    'main.link.rpc'             => '一个远程调用 json rpc 的例子（nginx 限定）',
    'main.link.dbtest_in'       => '（需要 sqlite）dbtest.php 数据库演示（框架内模式）',
    'main.link.dbtest_out'      => 'dbtest.php 数据库演示（框架外模式）',
    'main.link.cover_test'      => 'cover_test.php 覆盖率测试',
    'main.current_url'          => '当前 URL 是',
    'main.switch_language'      => '语言',
    'main.language_en'          => 'English',
    'main.language_zh'          => '中文',
];
