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
use DuckPhp\Core\SuperGlobal;

class RouteHookWebInstaller extends ComponentBase
{
    public $options = [
        'web_installer_path' => 'install',
        'web_installer_use_database' => true,
        'web_installer_use_redis' => false,
        'web_installer_local_redis' => false,
        'web_installer_database_drivers' => ['sqlite' => true, 'pgsql' => true],
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
        if (__url($this->options['web_installer_path']) !== __url($path_info)) {
            return false;
        }
        $this->installAction();

        return true;
    }
    public function installAction()
    {
        if ($this->isInstalled() && !$this->options['web_installer_force']) {
            $this->renderPage($this->buildPageData([]));
            return;
        }
        $post = SuperGlobal::_()->_POST();
        $post = is_array($post) ? $post : [];
        $ext_data = [];
        if (!empty($post)) {
            $ext_data = $this->installBusiness($post);
        }
        $data = $this->buildPageData($post);
        $data = array_merge($data, $ext_data);

        $this->renderPage($data);
    }

    protected function isInstalled(): bool
    {
        return !empty(App::_()->options['installed']);
    }
    //////////////////
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function buildPageData(array $post): array
    {
        $base = [
            'flash_message' => $this->flash_message,
            'error_message' => $this->error_message,
            'installed' => $this->isInstalled(),
            'use_database' => (bool) $this->options['web_installer_use_database'],
            'use_redis' => (bool) $this->options['web_installer_use_redis'],
            'web_installer_path' => $this->options['web_installer_path'],
            'checks' => $this->checkEnv(),
            'driver_options' => $this->getDatabaseDriverOptions(),
            'controller_resource_prefix' => (string) (App::_()->options['controller_resource_prefix'] ?? ''),
            'schema_path' => $this->getSchemaPath(),
            'database_list' => App::_()->options['database_list'] ?? [],
            'redis_list' => App::_()->options['redis_list'] ?? [],
        ];
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
            $options .= '<option value="__root__">Use root app database ('.__h($root_config['driver'] ?? 'unknown').')</option>';
        }
        foreach ($drivers as $driver) {
            $options .= '<option value="'.__h($driver).'">'.__h($driver).'</option>';
        }
        return $options;
    }
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public function installBusiness(array $post): array
    {
        $action = (string) ($post['action'] ?? '');
        switch ($action) {
            case 'database':
                return $this->doDatabase($post);
            case 'schema':
                return $this->doSchema($post);
            case 'redis':
                return $this->doRedis($post);
            case 'done':
                return $this->doDone($post);
            default:
                // no known action: stay on the same page
        }
        return [];
    }
    //////////////////  env
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
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function doDatabase(array $post): array
    {
        $driver = (string) ($post['driver'] ?? '');
        if ($driver === '__root__') {
            $this->flash_message = 'Use root app database.';
            return [];
        }
        if (!in_array($driver, $this->getEnabledDrivers(), true)) {
            $this->error_message = 'Unsupported driver: '.__h($driver);
            return [];
        }
        $config = [
            'host' => (string) ($post['host'] ?? '127.0.0.1'),
            'port' => (string) ($post['port'] ?? ''),
            'dbname' => (string) ($post['dbname'] ?? ''),
            'username' => (string) ($post['username'] ?? ''),
            'password' => (string) ($post['password'] ?? ''),
        ];
        $dsn = $this->makeDsn($driver, $config);
        if ($dsn === null) {
            $this->error_message = 'Driver requires dbname: '.__h($driver);
            return [];
        }
        $error = $this->testConnection($dsn, $config['username'], $config['password']);
        if ($error !== null) {
            $this->error_message = 'Connection failed: '.__h($error);
            return [];
        }
        $config['dsn'] = $dsn;
        ExtOptionsLoader::_()->saveExtOptions(['database_list' => [$config]]);
        $this->flash_message = 'Database saved and connected.';
        return ['database_list' => [$config]];
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
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function doSchema(array $post): array
    {
        $driver = $this->getCurrentDriver();
        if ($driver === null) {
            $this->error_message = 'No database configured.';
            return [];
        }
        $schema_file = $this->getSchemaFile($driver);
        if (!is_file($schema_file)) {
            $this->error_message = 'Schema file not found: '.__h($schema_file);
            return [];
        }
        $force = !empty($post['force']);
        try {
            $pdo = $this->createPdo();
            if ($force) {
                $this->dropAllTables($pdo, $driver);
            }
            $sql = (string) file_get_contents($schema_file);
            $this->executeSql($pdo, $sql);
        } catch (\Throwable $ex) {
            $this->error_message = 'Schema error: '.__h($ex->getMessage());
            return [];
        }
        $this->flash_message = 'Tables created.';
        return [];
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
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function doRedis(array $post): array
    {
        $config = [
            'host' => (string) ($post['host'] ?? '127.0.0.1'),
            'port' => (string) ($post['port'] ?? '6379'),
            'auth' => (string) ($post['auth'] ?? ''),
            'select' => (string) ($post['select'] ?? '0'),
        ];
        $error = $this->testRedis($config);
        if ($error !== null) {
            $this->error_message = 'Redis connection failed: '.__h($error);
            return [];
        }
        ExtOptionsLoader::_()->saveExtOptions(['redis_list' => [$config]]);
        $this->flash_message = 'Redis saved and connected.';
        return ['redis_list' => [$config]];
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
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function doDone(array $post): array
    {
        ExtOptionsLoader::_()->saveExtOptions(['installed' => date(DATE_ATOM)]);
        $this->flash_message = 'Installed Successfully.';
        return [];
    }
    //////////////////
    /**
     * Show page: render all install info in a single page by built-in view.
     * @param array<string, mixed> $data
     */
    protected function show(array $data)
    {
        extract($data);
        $title = !empty($installed) ? 'Already Installed' : 'DuckPhp Web Installer';
        ?>
<!doctype html><html><head><meta charset="utf-8"><title><?=__h($title)?></title>
<style>
body{font-family:sans-serif;max-width:720px;margin:2em auto;color:#222}
table{border-collapse:collapse;width:100%}
td,th{border:1px solid #ccc;padding:4px 8px;text-align:left}
.ok{color:#0a0}.fail{color:#a00}
.error{color:#a00}
input,select,button{padding:4px 8px}
fieldset{border:1px solid #ccc;margin:1em 0;padding:0 1em 1em}
legend{font-weight:bold}
</style></head><body>
<h1>DuckPhp Web Installer</h1>
<?php if (!empty($installed)): ?>
<h2>Already Installed</h2>
<?php if (!empty($flash_message)): ?><p class="ok"><?=__h((string)$flash_message)?></p><?php endif; ?>
<p>The application is already installed. To reinstall, please remove the <code>installed</code> entry from the ext options data file.</p>
<?php else: ?>
<?php if (!empty($error_message)): ?>
<p class="error"><?=__h((string)$error_message)?></p>
<?php endif; ?>
<?php if (!empty($flash_message)): ?>
<p class="ok"><?=__h((string)$flash_message)?></p>
<?php endif; ?>
<p>Current controller_resource_prefix: <code><?=__h((string)($controller_resource_prefix ?? ''))?></code></p>
<fieldset>
<legend>Environment Check</legend>
<table>
<thead><tr><th>Item</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($checks ?? [] as $item): ?>
<tr><td><?=__h((string)$item[1])?></td><td class="<?=$item[0]?'ok':'fail'?>"><?=$item[0]?'OK':'FAIL'?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</fieldset>
<?php if (!empty($use_database)): ?>
<fieldset>
<legend>Database Config</legend>
<?php if (!empty($database_list)): ?>
<p class="ok">Current database: <code><?=__h((string)$database_list[0]['dsn'])?></code></p>
<?php endif; ?>
<form method="post">
<input type="hidden" name="action" value="database">
<p><label>Driver: <select name="driver"><?=$driver_options ?? ''?></select></label></p>
<p><label>Host: <input type="text" name="host" value="127.0.0.1"></label></p>
<p><label>Port: <input type="text" name="port" value=""></label></p>
<p><label>Database: <input type="text" name="dbname" value=""></label></p>
<p><label>Username: <input type="text" name="username" value=""></label></p>
<p><label>Password: <input type="password" name="password" value=""></label></p>
<p><button type="submit">Test Connection &amp; Save</button></p>
</form>
</fieldset>
<fieldset>
<legend>Create Tables</legend>
<form method="post">
<input type="hidden" name="action" value="schema">
<p>Schema files will be loaded from: <code><?=__h((string)($schema_path ?? ''))?></code></p>
<p><label><input type="checkbox" name="force" value="1"> Force reinstall (drop existing tables)</label></p>
<p><button type="submit">Create Tables</button></p>
</form>
</fieldset>
<?php endif; ?>
<?php if (!empty($use_redis)): ?>
<fieldset>
<legend>Redis Config</legend>
<?php if (!empty($redis_list)): ?>
<p class="ok">Current redis: <code><?=__h((string)$redis_list[0]['host'].':'.$redis_list[0]['port'])?></code></p>
<?php endif; ?>
<form method="post">
<input type="hidden" name="action" value="redis">
<p><label>Host: <input type="text" name="host" value="127.0.0.1"></label></p>
<p><label>Port: <input type="text" name="port" value="6379"></label></p>
<p><label>Auth: <input type="password" name="auth" value=""></label></p>
<p><label>Select: <input type="text" name="select" value="0"></label></p>
<p><button type="submit">Save Redis Config</button></p>
</form>
</fieldset>
<?php endif; ?>
<fieldset>
<legend>Install</legend>
<form method="post">
<input type="hidden" name="action" value="done">
<p><button type="submit">Finish Install</button></p>
</form>
</fieldset>
<?php endif; ?>
</body></html>
<?php
    }
}
