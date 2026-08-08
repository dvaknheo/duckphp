<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\CoreHelper;
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
        $this->error_message = '';
        $this->flash_message = '';
        if (!empty(App::_()->options['installed'])) {
            // installed;
            CoreHelper::Show302('');
            return ;
        }
        $post = SuperGlobal::_()->_POST();
        $post = is_array($post) ? $post : [];
        $ext_data = [];
        if (!empty($post)) {
            $ext_data = $this->installBusiness($post);
        }
        $data = $this->buildPageData($post);
        $data = array_merge($data, $ext_data);

        if ($this->options['web_installer_view']) {
            View::_()->_Show($data, $this->options['web_installer_view']);
        } else {
            $this->show($data);
        }
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
            'installed' => !empty(App::_()->options['installed']),
            'use_database' => (bool) $this->options['web_installer_use_database'],
            'use_redis' => (bool) $this->options['web_installer_use_redis'],
            'web_installer_path' => $this->options['web_installer_path'],
            'checks' => $this->checkEnv(),
            'driver_options' => $this->getDatabaseDriverOptions(),
            'controller_resource_prefix' => (string) (App::_()->options['controller_resource_prefix'] ?? ''),
            'database_list' => App::_()->options['database_list'] ?? [],
            'redis_list' => App::_()->options['redis_list'] ?? [],
            'redis_follow_root' => true,
            'root_redis_list' => App::Root()->options['redis_list'] ?? [],
        ];
        return $base;
    }
    protected function getDatabaseDriverOptions(): string
    {
        $drivers = $this->getEnabledDrivers();
        $options = '';
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
        $ext_data = [];
        if ($this->options['web_installer_use_redis']) {
            $ext_data = array_merge($ext_data, $this->doRedis($post));
            if ($this->error_message) {
                return $ext_data;
            }
        }

        if ($this->options['web_installer_use_database']) {
            $ext_data = array_merge($ext_data, $this->doDatabase($post));
            if ($this->error_message) {
                return $ext_data;
            }
            $ext_data = array_merge($ext_data, $this->doSchema($post));
            if ($this->error_message) {
                return $ext_data;
            }
        }
        $ext_data = array_merge($ext_data, $this->doDone($post));
        return $ext_data;
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
        return $ret;
    }
    //////////////////  database
    protected function getEnabledDrivers(): array
    {
        $ret = [];
        foreach ($this->options['web_installer_database_drivers'] as $driver => $enabled) {
            if ($enabled) {
                if(is_file($this->getSchemaFile($driver))) {
                    $ret[] = $driver;
                }
                $ret[] = $driver;
            }
        }
        return $ret;
    }
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function doDatabase(array $post): array
    {
        if (!empty($post['database_follow_root'])) {
            $root_list = App::Root()->options['database_list'] ?? [];
            if (empty($root_list)) {
                $this->error_message = 'No root database configured.';
                return [];
            }
            $this->flash_message = 'Use root database.';
            return [];
        }
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

    protected function getSchemaFile(string $driver): string
    {
        $path_sub = (string) $this->options['web_installer_schema_path'];
        $filename = $driver.'.sql';
        return $this->extendFullFile('', $path_sub, $filename);
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
            $list = App::Root()->options['database_list'] ?? [];
        }
        if (empty($list)) {
            return null;
        }
        return explode(':', ''.$list[0]['dsn'])[0];
    }
    protected function createPdo(): \PDO
    {
        $list = App::_()->options['database_list'];
        if (empty($list)) {
            $list = App::Root()->options['database_list'];
        }
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
        if (!empty($post['redis_follow_root'])) {
            $root_list = App::Root()->options['redis_list'] ?? [];
            if (empty($root_list)) {
                $this->error_message = 'No root redis configured.';
                return [];
            }
            $this->flash_message = 'Use root redis.';
            return [];
        }
        $config = [
            'host' => (string) ($post['redis_host'] ?? '127.0.0.1'),
            'port' => (string) ($post['redis_port'] ?? '6379'),
            'auth' => (string) ($post['redis_auth'] ?? ''),
            'select' => (string) ($post['redis_select'] ?? '0'),
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
<fieldset>
<legend>Environment Check</legend>
<p>Current controller_resource_prefix: <code><?=__h((string)($controller_resource_prefix ?? ''))?></code></p>
<table>
<thead><tr><th>Item</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($checks ?? [] as $item): ?>
<tr><td><?=__h((string)$item[1])?></td><td class="<?=$item[0]?'ok':'fail'?>"><?=$item[0]?'OK':'FAIL'?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</fieldset>
<?php if (!empty($use_redis)): ?>
<fieldset>
<legend>Redis Config</legend>
<p><label><input type="checkbox" name="redis_follow_root" value="1" checked onchange="toggleRedis(this)"> Follow Main Application</label></p>
<div>
<p data-redis-row><label>Host: <input type="text" name="redis_host" value="127.0.0.1"></label></p>
<p data-redis-row><label>Port: <input type="text" name="redis_port" value="6379"></label></p>
<p data-redis-row><label>Auth: <input type="password" name="redis_auth" value=""></label></p>
<p data-redis-row><label>Select: <input type="text" name="redis_select" value="0"></label></p>
</div>
</fieldset>
<?php endif; ?>
<?php if (!empty($use_database)): ?>
<fieldset>
<legend>Database Config</legend>
<p><label><input type="checkbox" name="database_follow_root" value="1" checked onchange="toggleDatabase(this)"> Follow Main Application</label></p>
<div>
<p data-db-row><label>Driver: <select name="driver"><?=$driver_options ?? ''?></select></label></p>
<p data-db-row><label>Host: <input type="text" name="host" value="127.0.0.1"></label></p>
<p data-db-row><label>Port: <input type="text" name="port" value=""></label></p>
<p data-db-row><label>Database: <input type="text" name="dbname" value=""></label></p>
<p data-db-row><label>Username: <input type="text" name="username" value=""></label></p>
<p data-db-row><label>Password: <input type="password" name="password" value=""></label></p>
</div>
<p><label><input type="checkbox" name="force" value="1"> Force reinstall (drop existing tables)</label></p>
</fieldset>
<?php endif; ?>
<fieldset>
<legend>Customer Setting</legend>
<p>Reserved for future extensions.</p>
</fieldset>
<form method="post">
<input type="hidden" name="action" value="install">
<p><button type="submit">Install</button></p>
</form>
<script>
function toggleRows(rows, show) {
    for (var i = 0; i < rows.length; i++) {
        rows[i].style.display = show ? '' : 'none';
    }
}
function toggleRedis(cb) {
    toggleRows(document.querySelectorAll('[data-redis-row]'), !cb.checked);
}
function toggleDatabase(cb) {
    toggleRows(document.querySelectorAll('[data-db-row]'), !cb.checked);
}
var __redis_cb = document.querySelector('[name="redis_follow_root"]');
if (__redis_cb) { toggleRedis(__redis_cb); }
var __db_cb = document.querySelector('[name="database_follow_root"]');
if (__db_cb) { toggleDatabase(__db_cb); }
</script>
<?php endif; ?>
</body></html>
<?php
    }
}
