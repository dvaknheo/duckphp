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
        ob_start();
        $ret = RouteHookWebInstaller::Hook($path_info);
        $out = (string) ob_get_clean();
        return [$ret, $out];
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookWebInstaller::class);
        $__SERVER = $_SERVER;
        $app = $this->initApp();

        // not installed, non-install path: pass through
        [$ret, $out] = $this->hook('');
        $this->assertFalse($ret);
        $this->assertSame('', $out);

        // not installed, install path GET: show env step
        $_GET['step'] = 'env';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Environment Check', $out);
        $this->assertStringContainsString('PDO driver: sqlite', $out);

        // POST database: sqlite
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'database',
            'driver' => 'sqlite',
            'dbname' => $this->getTestPath().'runtime/installer_test.sqlite',
            'host' => '127.0.0.1',
            'port' => '',
            'username' => '',
            'password' => '',
        ];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Create Tables', $out);
        $this->assertStringContainsString('Database saved', $out);
        $list = $app->options['database_list'];
        $this->assertNotEmpty($list);
        $this->assertStringContainsString('sqlite:', $list[0]['dsn']);

        // POST schema: force reinstall
        $_POST = ['action' => 'schema', 'force' => '1'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Install Complete', $out);
        $this->assertStringContainsString('Tables created', $out);
        $pdo = new \PDO($list[0]['dsn']);
        $this->assertSame('demo', $pdo->query('select name from install_demo')->fetchColumn());

        // POST done
        $_POST = ['action' => 'done'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Installed Successfully', $out);
        $this->assertNotEmpty($app->options['installed']);

        // installed, install path: already-installed page
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Already Installed', $out);

        // installed, non-install path: pass through
        [$ret, $out] = $this->hook('');
        $this->assertFalse($ret);
        $this->assertSame('', $out);

        ///////////////// branch: unsupported driver
        $this->initApp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'database', 'driver' => 'oracle', 'dbname' => 'x'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Unsupported driver', $out);

        // branch: connection failed
        $_POST = ['action' => 'database', 'driver' => 'sqlite', 'dbname' => '/nonexistent_dir_xyz/1.sqlite'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Connection failed', $out);

        // branch: schema file missing (pgsql no file)
        $_POST = ['action' => 'database', 'driver' => 'sqlite', 'dbname' => $this->getTestPath().'runtime/installer_test2.sqlite'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        // remove sqlite.sql temporarily? use pgsql: getCurrentDriver is sqlite... keep simple: 
        // overwrite schema path via options
        RouteHookWebInstaller::_()->options['web_installer_schema_path'] = 'config_missing';
        $_POST = ['action' => 'schema'];
        [$ret, $out] = $this->hook('install');
        $this->assertTrue($ret);
        $this->assertStringContainsString('Schema file not found', $out);
        RouteHookWebInstaller::_()->options['web_installer_schema_path'] = 'config';

        ///////////////// use_redis = true app
        $this->initApp([], ['web_installer_use_redis' => true, 'web_installer_local_redis' => true]);
        // env shows redis check
        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Redis extension', $out);
        // database
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'database', 'driver' => 'sqlite', 'dbname' => $this->getTestPath().'runtime/installer_test3.sqlite'];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Create Tables', $out);
        // schema -> goes to redis step
        $_POST = ['action' => 'schema'];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Redis Config', $out);
        // redis
        $_POST = ['action' => 'redis', 'host' => '127.0.0.1', 'port' => '6379', 'auth' => '123456', 'select' => '0'];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Install Complete', $out);
        // done
        $_POST = ['action' => 'done'];
        [$ret, $out] = $this->hook('install');
        $this->assertStringContainsString('Installed Successfully', $out);

        ///////////////// use_database = false
        $this->initApp([], ['web_installer_use_database' => false, 'web_installer_use_redis' => false]);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['step' => 'database'];
        [$ret, $out] = $this->hook('install');
        // database skipped -> schema shown
        $this->assertStringContainsString('Create Tables', $out);

        // cleanup
        @unlink($this->getTestPath().'runtime/DuckPhpData.config.json');
        @unlink($this->getTestPath().'runtime/installer_test.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test2.sqlite');
        @unlink($this->getTestPath().'runtime/installer_test3.sqlite');
        clearstatcache();
        $_SERVER = $__SERVER;
        \LibCoverage\LibCoverage::End();
    }
}
class WebInstallerApp extends DuckPhp
{
    public $options = [
        'name' => 'WebInstallerApp',
    ];
}
