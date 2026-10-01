<?php declare(strict_types=1);
/**
 * DuckPHP demo - English sentences.
 *
 * One key per sentence; `lang-zh_CN.php` holds the translations of the very same keys.
 * Keys are FLAT: the dot is just part of the name, `Lang` does not walk a path
 * (`'a.b'` is looked up as the literal key `a.b`).
 *
 * The demo app sets `lang_default => 'en'`, and `zh_CN` is picked up from `?lang=`,
 * the `lang` cookie or the `Accept-Language` header (`lang_detect_mode`).
 *
 *   demo/view/main.php      → main.*        the welcome page
 *
 * A key missing here falls back to the key itself (see `lang_warn_on_missing`).
 */
return [
    'hello'                     => 'Hello',

    'main.welcome'              => 'Welcome to DuckPhp,',
    'main.see_demo'             => 'See the demo result',
    'main.doc_in_app'           => 'DuckPhp docs (in-framework mode)',
    'main.doc_standalone'       => 'DuckPhp docs (standalone page)',
    'main.examples_blurb'       => 'Common examples, none of them needs extra configuration',
    'main.link.files'           => '/files - sample stack and included files',
    'main.link.demo'            => 'demo.php - every feature in a single file',
    'main.link.helloworld'      => 'helloworld.php - the usual hello world',
    'main.link.just_route'      => 'just-route.php - routing only',
    'main.link.api'             => 'api.php - an API server example, no controllers needed',
    'main.link.traditional'     => 'traditional.php - traditional mode, one file does it all',
    'main.link.rpc'             => 'a JSON-RPC remote-call example (needs nginx)',
    'main.link.dbtest_in'       => '(needs sqlite) dbtest.php - database demo (in-framework mode)',
    'main.link.dbtest_out'      => 'dbtest.php - database demo (standalone mode)',
    'main.link.cover_test'      => 'cover_test.php - coverage report',
    'main.current_url'          => 'The current URL is',
    'main.switch_language'      => 'Language',
    'main.language_en'          => 'English',
    'main.language_zh'          => '中文',

    'files.elapsed_time'        => 'Elapsed time',
    'files.elapsed'             => 'Elapsed: ',
    'files.seconds'             => 's',
    'files.memory'              => 'Peak memory: ',
    'files.bytes'               => 'bytes',
    'files.singletons'          => 'All singletons',
    'files.app_options'         => 'Options of this app',
    'files.all_options'         => 'All options',
    'files.total'               => 'Total',
    'files.total_unit'          => ' options',
    'files.stack'               => 'Call stack down to the View layer',
    'files.included'            => 'Included files down to the View layer',
    'files.vendor_ignored'      => 'the vendor directory is ignored',
    'files.public_methods'      => 'Public methods of the DuckPhp class',
    'files.all_methods'         => 'All methods of the DuckPhp class',

    'dbtest.records'            => 'Records',
    'dbtest.content'            => 'Content',
    'dbtest.edit'               => 'Edit',
    'dbtest.delete'             => 'Delete',
    'dbtest.add'                => 'Add',
    'dbtest.view_edit'          => 'View / edit',
    'dbtest.original'           => 'Original content',
    'dbtest.back_home'          => 'Back to the list',

    'traditional.page_title'    => 'DuckPhp single-page demo',
    'traditional.home'          => 'Home',
    'traditional.empty'         => 'No content yet,',
    'traditional.add_content'   => 'Add content',
    'traditional.has_content'   => 'Content entered:',
    'traditional.edit_content'  => 'Edit content',
    'traditional.delete_content'=> 'Delete content (GET-safe)',
    'traditional.add'           => 'Add',
    'traditional.edit'          => 'Edit',
    'traditional.done'          => 'Done',
    'traditional.back_home'     => 'Back to the home page',
    'traditional.verify_failed' => 'Verification failed',

    'api.usage'                 => "    No parameters: {url}\n    With parameters: {url2} (passed on to the reflected parameters)\n    To change `uid`, extend RouteHookApiServer and override getObjectAndMethod() and getInputs()",

    'rpc.result'                => "local call 1 + 2 = {local} <br />\nremote call 3 + 4 = {remote1} <br />\nremote call 5 + 6 = {remote2} <br />\ncall time {date}",
];
