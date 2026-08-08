<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;

class RouteHookWebInstaller extends ComponentBase
{
    public $options = [
        'web_installer_path' => 'install',
        'web_installer_use_database' => true,
        'web_installer_use_redis' => false,
        'web_installer_local_redis' => false,
        'web_installer_database_drivers' => ['mysql' => true, 'sqlite' => true, 'pgsql' => true],
        'web_installer_schema_path' => 'config',
        'web_installer_view' => '',
        'web_installer_force' => false,
    ];
    protected $error_message = '';
    protected $flash_message = '';

    public static function Hook($path_info)
    {
        return static::_()->_Hook($path_info);
    }
    //@override
    protected function initContext(object $context): void
    {
        Route::_()->addRouteHook([static::class, 'Hook'], 'prepend-inner');
    }
    public function _Hook(string $path_info): bool
    {
        $is_install_path = $this->isInstallPath($path_info);
        if ($this->isInstalled()) {
            if ($is_install_path) {
                $this->showAlreadyInstalled();
                return true;
            }
            return false;
        }
        if (!$is_install_path) {
            return false;
        }
        if ($this->isPost()) {
            $this->installBusiness();
        } else {
            $this->installAction();
        }
        return true;
    }
    protected function isInstallPath(string $path_info): bool
    {
        $path = $this->options['web_installer_path'];
        return $path_info === $path || $path_info === $path.'/';
    }
    protected function isInstalled(): bool
    {
        return !empty(App::_()->options['installed']);
    }
    protected function isPost(): bool
    {
        $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        return strtoupper((string) ($my_server['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }
    protected function getStep(): string
    {
        $my_get = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_GET : $_GET;
        $step = (string) ($my_get['step'] ?? 'env');
        if (!in_array($step, ['env', 'database', 'schema', 'redis', 'done'], true)) {
            $step = 'env';
        }
        return $step;
    }
    protected function getPost(string $key, $default = null)
    {
        $my_post = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_POST : $_POST;
        return $my_post[$key] ?? $default;
    }
    //////////////////
    public function installAction()
    {
        $step = $this->getStep();
        if (!$this->options['web_installer_use_database'] && $step === 'database') {
            $step = 'schema';
        }
        if (!$this->options['web_installer_use_redis'] && $step === 'redis') {
            $step = 'done';
        }
        $data = $this->buildPageData($step);
        $this->renderPage($data);
    }
    /**
     * @param string $step
     * @return array<string, mixed>
     */
    protected function buildPageData(string $step): array
    {
        $base = [
            'step' => $step,
            'flash_message' => $this->flash_message,
            'error_message' => $this->error_message,
        ];
        switch ($step) {
            case 'env':
                return $base + ['checks' => $this->checkEnv()];
            case 'database':
                return $base + [
                    'driver_options' => $this->getDatabaseDriverOptions(),
                    'controller_resource_prefix' => (string) (App::_()->options['controller_resource_prefix'] ?? ''),
                ];
            case 'schema':
                return $base + ['schema_path' => $this->getSchemaPath()];
            case 'redis':
                return $base;
            case 'done':
                return $base;
            case 'installed':
                return $base + ['web_installer_path' => $this->options['web_installer_path']];
            case 'already':
                return $base;
        }
        return $base;
    }
    /**
     * @param array<string, mixed> $data
     */
    protected function renderPage(array $data)
    {
        if ($this->options['web_installer_view']) {
            View::_()->_Show($data, $this->options['web_installer_view']);
        } else {
            $this->show($data);
        }
    }
    protected function getDatabaseDriverOptions(): string
    {
        $drivers = $this->getEnabledDrivers();
        $options = '';
        $root_config = $this->getRootDatabaseConfig();
        if ($root_config) {
            $options .= '<option value="__root__">Use root app database ('.htmlspecialchars($root_config['driver'] ?? 'unknown').')</option>';
        }
        foreach ($drivers as $driver) {
            $options .= '<option value="'.htmlspecialchars($driver).'">'.htmlspecialchars($driver).'</option>';
        }
        return $options;
    }
    public function installBusiness()
    {
        $action = ''.$this->getPost('action');
        switch ($action) {
            case 'database':
                $this->doDatabase();
                break;
            case 'schema':
                $this->doSchema();
                break;
            case 'redis':
                $this->doRedis();
                break;
            case 'done':
                $this->doDone();
                break;
            default:
                $this->showEnv();
        }
    }
    //////////////////  env
    protected function showEnv()
    {
        $this->renderPage($this->buildPageData('env'));
    }
    protected function checkEnv(): array
    {
        $ret = [];
        $ret[] = [version_compare(PHP_VERSION, '7.4.0', '>='), 'PHP version >= 7.4 ('.PHP_VERSION.')'];
        $ret[] = [extension_loaded('PDO'), 'PDO extension'];
        $drivers = [];
        foreach ($this->options['web_installer_database_drivers'] as $driver => $enabled) {
            if (!$enabled) {
                continue;
            }
            $ext = 'pdo_'.$driver;
            $ret[] = [extension_loaded($ext), 'PDO driver: '.$driver];
            $drivers[] = $driver;
        }
        if ($this->options['web_installer_use_redis']) {
            $ret[] = [extension_loaded('redis'), 'Redis extension'];
        }
        $schema_path = $this->getSchemaPath();
        $ret[] = [is_dir($schema_path), 'Schema directory writable: '.$schema_path];
        return $ret;
    }
    //////////////////  database
    protected function showDatabase()
    {
        $this->renderPage($this->buildPageData('database'));
    }
    protected function getEnabledDrivers(): array
    {
        $ret = [];
        foreach ($this->options['web_installer_database_drivers'] as $driver => $enabled) {
            if ($enabled) {
                $ret[] = $driver;
            }
        }
        return $ret;
    }
    protected function getRootDatabaseConfig()
    {
        $root = App::Root();
        $list = $root->options['database_list'] ?? [];
        if (empty($list)) {
            return null;
        }
        $config = $list[0];
        $config['driver'] = explode(':', ''.$config['dsn'])[0];
        return $config;
    }
    protected function doDatabase()
    {
        $driver = ''.$this->getPost('driver');
        if ($driver === '__root__') {
            $this->flash_message = 'Use root app database.';
            $this->showSchema();
            return;
        }
        if (!in_array($driver, $this->getEnabledDrivers(), true)) {
            $this->error_message = 'Unsupported driver: '.htmlspecialchars($driver);
            $this->showDatabase();
            return;
        }
        $config = [
            'host' => ''.$this->getPost('host', '127.0.0.1'),
            'port' => ''.$this->getPost('port', ''),
            'dbname' => ''.$this->getPost('dbname', ''),
            'username' => ''.$this->getPost('username', ''),
            'password' => ''.$this->getPost('password', ''),
        ];
        $dsn = $this->makeDsn($driver, $config);
        if ($dsn === null) {
            $this->error_message = 'Driver requires dbname: '.htmlspecialchars($driver);
            $this->showDatabase();
            return;
        }
        $error = $this->testConnection($dsn, $config['username'], $config['password']);
        if ($error !== null) {
            $this->error_message = 'Connection failed: '.htmlspecialchars($error);
            $this->showDatabase();
            return;
        }
        $config['dsn'] = $dsn;
        ExtOptionsLoader::_()->saveExtOptions(['database_list' => [$config]]);
        $this->flash_message = 'Database saved and connected.';
        $this->showSchema();
    }
    protected function makeDsn(string $driver, array $config): ?string
    {
        if ($driver === 'sqlite') {
            return 'sqlite:'.($config['dbname'] ?: 'database.sqlite');
        }
        if (empty($config['dbname'])) {
            return null;
        }
        $dsn = $driver.':host='.$config['host'].';dbname='.$config['dbname'];
        if (!empty($config['port'])) {
            $dsn .= ';port='.$config['port'];
        }
        return $dsn;
    }
    protected function testConnection(string $dsn, string $username, string $password): ?string
    {
        try {
            $pdo = new \PDO($dsn, $username ?: null, $password ?: null, [\PDO::ATTR_TIMEOUT => 3, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $pdo->query('select 1');
            return null;
        } catch (\Throwable $ex) {
            return $ex->getMessage();
        }
    }
    //////////////////  schema
    protected function showSchema()
    {
        $this->renderPage($this->buildPageData('schema'));
    }
    protected function getSchemaPath(): string
    {
        $schema_path = (string) $this->options['web_installer_schema_path'];
        if (static::IsAbsPath($schema_path)) {
            return rtrim($schema_path, '/');
        }
        $path = App::Root()->options['path'] ?? '';
        return rtrim((string)$path, '/').'/'.trim($schema_path, '/');
    }
    protected function getSchemaFile(string $driver): string
    {
        return $this->getSchemaPath().'/'.$driver.'.sql';
    }
    protected function doSchema()
    {
        $driver = $this->getCurrentDriver();
        if ($driver === null) {
            $this->error_message = 'No database configured.';
            $this->showDatabase();
            return;
        }
        $schema_file = $this->getSchemaFile($driver);
        if (!is_file($schema_file)) {
            $this->error_message = 'Schema file not found: '.htmlspecialchars($schema_file);
            $this->showSchema();
            return;
        }
        $force = (bool) $this->getPost('force');
        try {
            $pdo = $this->createPdo();
            if ($force) {
                $this->dropAllTables($pdo, $driver);
            }
            $sql = (string) file_get_contents($schema_file);
            $this->executeSql($pdo, $sql);
        } catch (\Throwable $ex) {
            $this->error_message = 'Schema error: '.htmlspecialchars($ex->getMessage());
            $this->showSchema();
            return;
        }
        $this->flash_message = 'Tables created.';
        if ($this->options['web_installer_use_redis']) {
            $this->showRedis();
        } else {
            $this->showDone();
        }
    }
    protected function getCurrentDriver(): ?string
    {
        $list = App::_()->options['database_list'] ?? [];
        if (empty($list)) {
            return null;
        }
        return explode(':', ''.$list[0]['dsn'])[0];
    }
    protected function createPdo(): \PDO
    {
        $list = App::_()->options['database_list'];
        $config = $list[0];
        return new \PDO($config['dsn'], $config['username'] ?? null, $config['password'] ?? null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }
    protected function dropAllTables(\PDO $pdo, string $driver): void
    {
        if ($driver === 'sqlite') {
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $pdo->exec('DROP TABLE IF EXISTS "'.str_replace('"', '""', ''.$table).'"');
            }
            return;
        }
        $schema = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($schema as $table) {
            $pdo->exec('DROP TABLE IF EXISTS `'.str_replace('`', '``', ''.$table).'`');
        }
    }
    protected function executeSql(\PDO $pdo, string $sql): void
    {
        $driver = $this->getCurrentDriver();
        if ($driver === 'pgsql') {
            foreach (preg_split('/;\s*(\n|$)/', $sql) as $statement) {
                $statement = trim($statement);
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
            }
            return;
        }
        $pdo->exec($sql);
    }
    //////////////////  redis
    protected function showRedis()
    {
        $this->renderPage($this->buildPageData('redis'));
    }
    protected function doRedis()
    {
        $config = [
            'host' => ''.$this->getPost('host', '127.0.0.1'),
            'port' => ''.$this->getPost('port', '6379'),
            'auth' => ''.$this->getPost('auth', ''),
            'select' => ''.$this->getPost('select', '0'),
        ];
        $error = $this->testRedis($config);
        if ($error !== null) {
            $this->error_message = 'Redis connection failed: '.htmlspecialchars($error);
            $this->showRedis();
            return;
        }
        ExtOptionsLoader::_()->saveExtOptions(['redis_list' => [$config]]);
        $this->flash_message = 'Redis saved and connected.';
        $this->showDone();
    }
    protected function testRedis(array $config): ?string
    {
        if (!class_exists(\Redis::class)) {
            return 'Redis extension not loaded';
        }
        try {
            $redis = new \Redis();
            $redis->connect($config['host'], (int) $config['port'], 3);
            if (!empty($config['auth'])) {
                $redis->auth($config['auth']);
            }
            if ('' !== $config['select']) {
                $redis->select((int) $config['select']);
            }
            $redis->ping();
            return null;
        } catch (\Throwable $ex) {
            return $ex->getMessage();
        }
    }
    //////////////////  done
    protected function showDone()
    {
        $this->renderPage($this->buildPageData('done'));
    }
    protected function doDone()
    {
        ExtOptionsLoader::_()->saveExtOptions(['installed' => date(DATE_ATOM)]);
        $this->renderPage($this->buildPageData('installed'));
    }
    //////////////////
    protected function showAlreadyInstalled()
    {
        $this->renderPage($this->buildPageData('already'));
    }
    /**
     * Show page: render all page info by built-in view.
     * @param array<string, mixed> $data
     */
    protected function show(array $data)
    {
        extract($data);
        $title = [
            'env' => 'Environment Check',
            'database' => 'Database Config',
            'schema' => 'Create Tables',
            'redis' => 'Redis Config',
            'done' => 'Install Complete',
            'installed' => 'Installed',
            'already' => 'Already Installed',
        ][$step ?? ''] ?? 'DuckPhp Web Installer';
        ?>
<!doctype html><html><head><meta charset="utf-8"><title><?=htmlspecialchars($title)?></title>
<style>
body{font-family:sans-serif;max-width:640px;margin:2em auto;color:#222}
table{border-collapse:collapse;width:100%}
td,th{border:1px solid #ccc;padding:4px 8px;text-align:left}
.ok{color:#0a0}.fail{color:#a00}
.error{color:#a00}
input,select,button{padding:4px 8px}
</style></head><body>
<h1>DuckPhp Web Installer</h1>
<?php if (!empty($error_message)): ?>
<p class="error"><?=htmlspecialchars((string)$error_message)?></p>
<?php endif; ?>
<?php if (!empty($flash_message)): ?>
<p class="ok"><?=htmlspecialchars((string)$flash_message)?></p>
<?php endif; ?>
<?php switch ($step ?? '') {
    case 'env': ?>
<h2>Step 1: Environment Check</h2>
<table>
<thead><tr><th>Item</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($checks ?? [] as $item): ?>
<tr><td><?=htmlspecialchars((string)$item[1])?></td><td class="<?=$item[0]?'ok':'fail'?>"><?=$item[0]?'OK':'FAIL'?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<form method="post" action="?step=env">
<input type="hidden" name="action" value="env">
<button type="submit">Next</button>
</form>
<?php break;
    case 'database': ?>
<h2>Step 2: Database Config</h2>
<p>Current controller_resource_prefix: <code><?=htmlspecialchars((string)($controller_resource_prefix ?? ''))?></code></p>
<form method="post" action="?step=database">
<input type="hidden" name="action" value="database">
<p><label>Driver: <select name="driver"><?=$driver_options ?? ''?></select></label></p>
<p><label>Host: <input type="text" name="host" value="127.0.0.1"></label></p>
<p><label>Port: <input type="text" name="port" value=""></label></p>
<p><label>Database: <input type="text" name="dbname" value=""></label></p>
<p><label>Username: <input type="text" name="username" value=""></label></p>
<p><label>Password: <input type="password" name="password" value=""></label></p>
<p><button type="submit" name="test" value="1">Test Connection &amp; Save</button></p>
</form>
<?php break;
    case 'schema': ?>
<h2>Step 3: Create Tables</h2>
<form method="post" action="?step=schema">
<input type="hidden" name="action" value="schema">
<p>Schema files will be loaded from: <code><?=htmlspecialchars((string)($schema_path ?? ''))?></code></p>
<p><label><input type="checkbox" name="force" value="1"> Force reinstall (drop existing tables)</label></p>
<p><button type="submit">Create Tables</button></p>
</form>
<?php break;
    case 'redis': ?>
<h2>Step 4: Redis Config</h2>
<form method="post" action="?step=redis">
<input type="hidden" name="action" value="redis">
<p><label>Host: <input type="text" name="host" value="127.0.0.1"></label></p>
<p><label>Port: <input type="text" name="port" value="6379"></label></p>
<p><label>Auth: <input type="password" name="auth" value=""></label></p>
<p><label>Select: <input type="text" name="select" value="0"></label></p>
<p><button type="submit">Save Redis Config</button></p>
</form>
<?php break;
    case 'done': ?>
<h2>Step 5: Install Complete</h2>
<form method="post" action="?step=done">
<input type="hidden" name="action" value="done">
<p><button type="submit">Finish Install</button></p>
</form>
<?php break;
    case 'installed': ?>
<h2>Installed Successfully</h2>
<p class="ok">The application is now installed. Go to <a href="<?=htmlspecialchars((string)($web_installer_path ?? ''))?>">home page</a>.</p>
<?php break;
    case 'already': ?>
<h2>Already Installed</h2>
<p>The application is already installed. To reinstall, please remove the <code>installed</code> entry from the ext options data file.</p>
<?php break;
} ?>
</body></html>
<?php
    }
}
