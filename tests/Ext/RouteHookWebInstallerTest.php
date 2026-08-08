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
    protected function initApp(array $extra = [], array $component_options = [])
    {
        $path_app = $this->getTestPath();
        @unlink($path_app.'runtime/DuckPhpData.config.json');
        clearstatcache();
        $options = array_merge([
            'path' => $path_app,
            'ext_options_file_enable' => true,
            'ext' => [
                RouteHookWebInstaller::class => array_merge([
                    'web_installer_schema_path' => realpath(__DIR__.'/../data_for_tests/Ext/RouteHookWebInstaller/config'),
                ], $component_options),
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
        $ret = RouteHookWebInstaller::Hook($path_info);
        $out = (string) ob_get_clean();
        return [$ret, $out];
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookWebInstaller::class);
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
            'dbname' => $this->getTestPath().'runtime/installer_test.sqlite',
            'host' => '127.0.0.1',
            'port' => '',
            'username' => '',
            'password' => '',
            'force' => '1',
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Already Installed', $out);
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
        $_POST = ['action' => 'install', 'driver' => 'oracle', 'dbname' => 'x'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Unsupported driver', $out);
        $this->assertStringNotContainsString('Installed Successfully', $out);

        // branch: connection failed
        $_POST = ['action' => 'install', 'driver' => 'sqlite', 'dbname' => '/nonexistent_dir_xyz/1.sqlite'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Connection failed', $out);

        // branch: schema file missing
        $config_schema = $this->getTestPath().'config/sqlite.sql';
        $schema_backup = $config_schema.'.bak';
        rename($config_schema, $schema_backup);
        $_POST = ['action' => 'install', 'driver' => 'sqlite', 'dbname' => $this->getTestPath().'runtime/installer_test2.sqlite'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Schema file not found', $out);
        rename($schema_backup, $config_schema);

        ///////////////// multi-database: two sqlite configs
        $app = $this->initApp([], ['web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'install',
            'driver' => ['sqlite', 'sqlite'],
            'dbname' => [$this->getTestPath().'runtime/installer_test_a.sqlite', $this->getTestPath().'runtime/installer_test_b.sqlite'],
            'force' => '1',
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Already Installed', $out);
        $this->assertCount(2, $app->options['database_list']);
        $this->assertStringContainsString('installer_test_b.sqlite', $app->options['database_list'][1]['dsn']);
        // schema built into the first database
        $pdo = new \PDO($app->options['database_list'][0]['dsn']);
        $this->assertSame('demo', $pdo->query('select name from install_demo')->fetchColumn());

        ///////////////// use_redis = true app: multi-redis
        $app = $this->initApp([], ['web_installer_use_redis' => true, 'web_installer_local_redis' => true]);
        // GET: single page with redis check and redis section
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Redis extension', $out);
        $this->assertStringContainsString('Redis Config', $out);
        // one-shot install with database + schema + multi-redis + done
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'install',
            'driver' => 'sqlite',
            'dbname' => $this->getTestPath().'runtime/installer_test3.sqlite',
            'host' => '127.0.0.1',
            'port' => '',
            'username' => '',
            'password' => '',
            'force' => '1',
            'redis_host' => ['127.0.0.1', '127.0.0.1'],
            'redis_port' => ['6379', '6379'],
            'redis_auth' => ['123456', '123456'],
            'redis_select' => ['0', '1'],
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Already Installed', $out);
        $this->assertNotEmpty($app->options['redis_list']);
        $this->assertCount(2, $app->options['redis_list']);
        $this->assertSame('1', $app->options['redis_list'][1]['select']);

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

        // cleanup
        @unlink($this->getTestPath().'runtime/DuckPhpData.config.json');
        @unlink($this->getTestPath().'runtime/installer_test.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test2.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test3.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test_a.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test_b.sqlite');
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
