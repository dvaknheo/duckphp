<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookWebInstaller;
use DuckPhp\DuckPhp;

class RouteHookWebInstallerTest extends \PHPUnit\Framework\TestCase
{
    protected function getTestPath(): string
    {
        return \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
    }
    protected $installer_class = RouteHookWebInstaller::class;
    protected function initApp(array $extra = [], array $component_options = [], string $installer_class = RouteHookWebInstaller::class)
    {
        $this->installer_class = $installer_class;
        $path_app = $this->getTestPath();
        @mkdir($path_app.'runtime', 0777, true);
        @unlink($path_app.'runtime/DuckPhpData.config.json');
        clearstatcache();
        $options = array_merge([
            'path' => $path_app,
            'path_config' => realpath(__DIR__.'/../data_for_tests/Ext/RouteHookWebInstaller/config'),
            'ext_options_file_enable' => true,
            'ext' => [
                $installer_class => array_merge([], $component_options),
            ],
        ], $extra);
        \DuckPhp\Core\PhaseContainer::RestAllContainerForTesting();
        $app = new WebInstallerApp();
        DuckPhp::_($app);
        $app->init($options);
        return $app;
    }
    protected function hook(string $path_info): array
    {
        \DuckPhp\Core\SuperGlobal::LoadSuperGlobalAll();
        ob_start();
        $class = $this->installer_class;
        $ret = $class::Hook($path_info);
        $out = (string) ob_get_clean();
        return [$ret, $out];
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::G()->addExtFile(__DIR__.'/../../src/Ext/RouteHookWebInstallerView.php');
        \LibCoverage\LibCoverage::Begin(RouteHookWebInstaller::class);
        // clean leftover artifacts from previous runs (failed tests leave them behind)
        $test_path = $this->getTestPath();
        @unlink($test_path.'config/sqlite.sql');
        @unlink($test_path.'config/sqlite.clean.sql');
        @unlink($test_path.'config/sqlite.data.sql');
        @unlink($test_path.'view/custom_block.php');
        $__SERVER = $_SERVER;
        $app = $this->initApp([], ['web_installer_use_redis' => false]);

        // not installed, non-install path: pass through
        [$ret, $out] = $this->hook('');
        $this->assertFalse($ret);
        $this->assertSame('', $out);

        // not installed, install path GET: single page with all sections
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Environment Check', $out);
        $this->assertStringContainsString('PDO driver: sqlite', $out);
        $this->assertStringContainsString('Database Config', $out);
        $this->assertStringContainsString('Follow Main Application', $out);
        $this->assertStringContainsString('Install', $out);
        $this->assertStringNotContainsString('Redis Config', $out); // use_redis=false

        // POST unknown action: stay on same single page
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'unknown'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Environment Check', $out);

        // POST install: one-shot database + schema + done
        $_POST = [
            'action' => 'install',
            'driver' => 'sqlite',
            'database' => ['file' => $this->getTestPath().'runtime/installer_test.sqlite'],
            'force' => '1',
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Install Complete', $out);
        $this->assertStringContainsString('Redirecting to the homepage in 5 seconds.', $out);
        $this->assertStringContainsString('http-equiv="refresh"', $out);
        $list = $app->options['database_list'];
        $this->assertNotEmpty($list);
        $this->assertStringContainsString('sqlite:', $list[0]['dsn']);
        $this->assertNotEmpty($app->options['installed']);
        $pdo = new \PDO($list[0]['dsn']);
        $this->assertSame('demo', $pdo->query('select name from install_demo')->fetchColumn());

        // installed, install path: 302 redirect (no body)
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertSame('', $out);

        // installed, non-install path: pass through
        [$ret, $out] = $this->hook('');
        $this->assertFalse($ret);
        $this->assertSame('', $out);

        ///////////////// branch: install fails at database step (unsupported driver)
        $this->initApp([], ['web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'install', 'driver' => 'oracle', 'database' => ['dbname' => 'x']];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Unsupported driver', $out);
        $this->assertStringNotContainsString('Installed Successfully', $out);

        // branch: connection failed
        $_POST = ['action' => 'install', 'driver' => 'sqlite', 'database' => ['file' => '/nonexistent_dir_xyz/1.sqlite']];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('connection failed', $out);

        // branch: schema file missing
        $config_schema = realpath(__DIR__.'/../data_for_tests/Ext/RouteHookWebInstaller/config/sqlite.sql');
        $schema_backup = $config_schema.'.bak';
        rename($config_schema, $schema_backup);
        $_POST = ['action' => 'install', 'driver' => 'sqlite', 'database' => ['file' => $this->getTestPath().'runtime/installer_test2.sqlite']];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Schema file not found', $out);
        rename($schema_backup, $config_schema);

        ///////////////// multi-database: two sqlite configs
        $app = $this->initApp([], ['web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'install',
            'driver' => 'sqlite',
            'database' => ['file' => $this->getTestPath().'runtime/installer_test_a.sqlite'],
            'force' => '1',
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Install Complete', $out);
        $this->assertCount(1, $app->options['database_list']);
        $this->assertStringContainsString('installer_test_a.sqlite', $app->options['database_list'][0]['dsn']);
        // schema built into the database
        $pdo = new \PDO($app->options['database_list'][0]['dsn']);
        $this->assertSame('demo', $pdo->query('select name from install_demo')->fetchColumn());

        ///////////////// table_prefix: {prefix} placeholder replaced
        $app = $this->initApp(['table_prefix' => 't_'], ['web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'install',
            'driver' => 'sqlite',
            'database' => ['file' => $this->getTestPath().'runtime/installer_test_prefix.sqlite'],
            'force' => '1',
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Install Complete', $out);
        $pdo = new \PDO($app->options['database_list'][0]['dsn']);
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertContains('t_install_demo', $tables);
        $this->assertSame('demo', $pdo->query('select name from t_install_demo')->fetchColumn());

        ///////////////// use_redis = true app: single redis
        $app = $this->initApp([], ['web_installer_use_redis' => true, 'web_installer_local_redis' => true]);
        // GET: single page with redis check and redis section
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Redis extension', $out);
        $this->assertStringContainsString('Redis Config', $out);
        // one-shot install with database + schema + redis + done
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'install',
            'driver' => 'sqlite',
            'database' => ['file' => $this->getTestPath().'runtime/installer_test3.sqlite'],
            'force' => '1',
            'redis' => ['host' => '127.0.0.1', 'port' => '6379', 'auth' => '123456', 'select' => '0'],
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Install Complete', $out);
        $this->assertNotEmpty($app->options['redis_list']);
        $this->assertSame('6379', $app->options['redis_list'][0]['port']);

        ///////////////// use_database = false
        $this->initApp([], ['web_installer_use_database' => false, 'web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        // database sections skipped
        $this->assertStringContainsString('Environment Check', $out);
        $this->assertStringContainsString('Install', $out);
        $this->assertStringNotContainsString('Database Config', $out);
        $this->assertStringNotContainsString('Create Tables', $out);

        ///////////////// web_installer_path follows app url_install
        $app = $this->initApp(['url_install' => 'setup'], ['web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('setup');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Environment Check', $out);

        ///////////////// default: no Customer Setting rendered
        $this->initApp([], ['web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringNotContainsString('Customer Setting', $out);

        ///////////////// override renderCustom: custom block shown + post echo-back
        $this->initApp([], ['web_installer_use_redis' => false], RouteHookWebInstallerCustom::class);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'unknown', 'custom_key' => 'abc123'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Customer Setting', $out);
        $this->assertStringContainsString('Custom:', $out);
        $this->assertStringContainsString('abc123', $out);

        ///////////////// use_database=false + POST: checkDatabase early-returns []
        $this->initApp([], ['web_installer_use_database' => false, 'web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'install'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Install Complete', $out);

        ///////////////// redis connection failed (bad port)
        $app = $this->initApp([], ['web_installer_use_redis' => true, 'web_installer_local_redis' => true, 'web_installer_use_database' => false]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'install', 'redis' => ['host' => '127.0.0.1', 'port' => '1', 'auth' => '', 'select' => '0']];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Redis connection failed', $out);
        $this->assertStringNotContainsString('Installed Successfully', $out);

        ///////////////// pgsql without dbname: Driver requires dbname (server-type makeDsn)
        $this->initApp([], ['web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'install', 'driver' => 'pgsql', 'database' => ['host' => '127.0.0.1']];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Driver requires dbname', $out);

        ///////////////// pgsql with dbname: connection fails (no pgsql server; server-type makeDsn covered)
        $_POST = ['action' => 'install', 'driver' => 'pgsql', 'database' => ['host' => '127.0.0.1', 'port' => '5432', 'dbname' => 'x']];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('connection failed', $out);
        $this->assertStringNotContainsString('Installed Successfully', $out);

        ///////////////// string drivers config (single driver as plain string)
        $this->initApp([], ['web_installer_use_redis' => false, 'web_installer_database_drivers' => 'sqlite']);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('sqlite', $out);
        $this->assertStringNotContainsString('pgsql', $out);

        ///////////////// custom callbacks via options (render/check/do)
        $app = $this->initApp([], [
            'web_installer_use_redis' => false,
            'web_installer_render_custom_callback' => function ($post) { return '<p>Custom CB</p>'; },
            'web_installer_check_custom_callback' => function ($post) { return []; },
            'web_installer_do_custom_callback' => function ($post, $ext_data) { return []; },
        ]);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Custom CB', $out);

        ///////////////// custom check callback throws -> custom_error_message
        $this->initApp([], [
            'web_installer_use_redis' => false,
            'web_installer_check_custom_callback' => function ($post) { throw new \Exception('custom check fail'); },
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'install', 'driver' => 'sqlite', 'database' => ['file' => $this->getTestPath().'runtime/installer_test_b.sqlite']];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('custom check fail', $out);

        ///////////////// do_custom_callback runs on success (use_database=false skips schema)
        $this->initApp([], [
            'web_installer_use_database' => false,
            'web_installer_use_redis' => false,
            'web_installer_do_custom_callback' => function ($post, $ext_data) { return []; },
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'install'];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Install Complete', $out);

        ///////////////// do_custom_callback throws -> custom_error_message
        $this->initApp([], [
            'web_installer_use_database' => false,
            'web_installer_use_redis' => false,
            'web_installer_do_custom_callback' => function ($post, $ext_data) { throw new \Exception('custom do fail'); },
        ]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'install'];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('custom do fail', $out);
        $this->assertStringNotContainsString('Install Complete', $out);

        ///////////////// web_installer_view: external view used instead of built-in
        $view_file = $this->getTestPath().'view/installer_view.php';
        @mkdir(dirname($view_file), 0777, true);
        file_put_contents($view_file, '<p>ExternalView:<?=__h((string)($title ?? ""))?></p>');
        $this->initApp([], ['web_installer_use_redis' => false, 'web_installer_view' => $view_file]);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('ExternalView', $out);

        ///////////////// web_installer_view_block_custom: external view block rendered as Customer Setting
        $block_file = $this->getTestPath().'view/custom_block.php';
        file_put_contents($block_file, '<p>CustomBlock:<?=__h((string)($post[\'myfield\'] ?? ""))?></p>');
        $this->initApp([], ['web_installer_use_redis' => false, 'web_installer_use_database' => false, 'web_installer_view_block_custom' => $block_file]);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('CustomBlock:', $out);
        ///////////////// child app: local_redis / local_database written (non-root)
        $parent = new WebInstallerApp();
        DuckPhp::_($parent);
        $parent->init([
            'path' => $this->getTestPath(),
            'path_config' => realpath(__DIR__.'/../data_for_tests/Ext/RouteHookWebInstaller/config'),
            'ext_options_file_enable' => true,
            'app' => [
                WebInstallerChildApp::class => [
                    'path' => $this->getTestPath(),
                    'path_config' => realpath(__DIR__.'/../data_for_tests/Ext/RouteHookWebInstaller/config'),
                    'ext_options_file_enable' => true,
                    'ext' => [
                        RouteHookWebInstaller::class => [
                            'web_installer_use_redis' => true,
                        ],
                    ],
                ],
            ],
        ]);
        $child = $parent->toThisChild(WebInstallerChildApp::class);
        $this->assertNotNull($child);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'install',
            'driver' => 'sqlite',
            'database' => ['file' => $this->getTestPath().'runtime/installer_test_child.sqlite'],
            'force' => '1',
            'redis' => ['host' => '127.0.0.1', 'port' => '6379', 'auth' => '123456', 'select' => '0'],
        ];
        $ret = RouteHookWebInstaller::Hook('install');
        $this->assertTrue($ret);
        $this->assertNotEmpty($child->options['redis_list']);
        $this->assertTrue(!empty($child->options['local_redis']));
        $this->assertTrue(!empty($child->options['local_database']));

        // i18n: translation via lang_simple_mode_only_sentences + lang_default
        $_POST = [];
        $this->initApp([
            'lang_detect_mode' => ['default'],
            'lang_default' => 'zh_CN',
            'lang_simple_mode_only_sentences' => [
                'zh_CN' => [
                    'webinstaller.h1' => '鸭子网页安装器',
                    'webinstaller.env_check' => '环境检查',
                    'webinstaller.install' => '安装',
                ],
            ],
        ], ['web_installer_use_redis' => false, 'web_installer_use_database' => false]);
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('鸭子网页安装器', $out);
        $this->assertStringContainsString('环境检查', $out);
        $this->assertStringContainsString('>安装</button>', $out);
        $this->assertStringNotContainsString('Environment Check', $out);
        $this->assertStringNotContainsString('DuckPhp Web Installer', $out);

        // i18n: web_installer_default_sentences overrides built-in defaults
        $_POST = [];
        $this->initApp([], ['web_installer_use_redis' => false, 'web_installer_use_database' => false, 'web_installer_default_sentences' => [
            'webinstaller.h1' => 'My Installer',
        ]]);
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('My Installer', $out);
        $this->assertStringNotContainsString('DuckPhp Web Installer', $out);

        // cleanup
        @unlink($this->getTestPath().'runtime/DuckPhpData.config.json');
        @unlink($this->getTestPath().'runtime/installer_test.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test2.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test3.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test_a.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test_prefix.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test_child.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test_b.sqlite');
        @unlink($this->getTestPath().'view/installer_view.php');
        @unlink($this->getTestPath().'view/custom_block.php');
        @unlink($this->getTestPath().'config/sqlite.sql');
        @unlink($this->getTestPath().'config/sqlite.clean.sql');
        @unlink($this->getTestPath().'config/sqlite.data.sql');
        clearstatcache();
        $_SERVER = $__SERVER;
        $_POST = [];
        $_GET = [];
        \LibCoverage\LibCoverage::End();
    }
}
class WebInstallerApp extends DuckPhp
{
    public $options = [
        'name' => 'WebInstallerApp',
    ];
}
class WebInstallerChildApp extends DuckPhp
{
    public $options = [
        'name' => 'WebInstallerChildApp',
    ];
}
class RouteHookWebInstallerCustom extends RouteHookWebInstaller
{
    protected function renderCustom(array $view_data): string
    {
        $val = (string) ($view_data['post']['custom_key'] ?? 'default');
        return '<p><label>Custom: <input type="text" name="custom_key" value="'.htmlspecialchars($val).'"></label></p>';
    }
}
