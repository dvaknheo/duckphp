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
        'web_installer_use_redis' => true,
        'web_installer_database_drivers' => ['sqlite' => true, 'pgsql' => true, 'duckdb' => false],
        'web_installer_view' => '',
        'web_installer_force' => false,
    ];
    protected $drivers = null;
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
        if (!empty(App::_()->options['installed'])) {
            // installed;
            CoreHelper::Show302('');
            return ;
        }
        $post = SuperGlobal::_()->_POST();
        $post = is_array($post) ? $post : [];
        $post = $this->filterPost($post);
        $exceptions = [];
        $installed = false;
        if (!empty($post)) {
            $ret = $this->installBusiness($post);
            $exceptions = $ret['exceptions'] ?? [];
            $installed = !empty($ret['installed']);
        }
        $data = $this->buildPageData($post, $exceptions, $installed);

        if ($this->options['web_installer_view']) {
            View::_()->_Show($data, $this->options['web_installer_view']);
        } else {
            $this->show($data);
        }
    }
    /**
     * Filter POST input to a whitelist before use (avoid extract() injection).
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function filterPost(array $post): array
    {
        $whitelist = ['action', 'driver', 'host', 'port', 'dbname', 'username', 'password', 'force', 'redis_follow_root', 'redis_host', 'redis_port', 'redis_auth', 'redis_select', 'database_follow_root'];
        $ret = [];
        foreach ($whitelist as $key) {
            if (array_key_exists($key, $post)) {
                $ret[$key] = $post[$key];
            }
        }
        return $ret;
    }

    //////////////////
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $exceptions
     * @param bool $installed
     * @return array<string, mixed>
     */
    protected function buildPageData(array $post, array $exceptions = [], bool $installed = false): array
    {
        $redis_list = App::_()->options['redis_list'] ?? [];
        $database_list = App::_()->options['database_list'] ?? [];
        if (!empty($exceptions)) {
            // keep the posted config on failure so the user can retry with the same values
            if (isset($post['redis_host'])) {
                $redis_list = [[
                    'host' => (string) ($post['redis_host'] ?? '127.0.0.1'),
                    'port' => (string) ($post['redis_port'] ?? '6379'),
                    'auth' => (string) ($post['redis_auth'] ?? ''),
                    'select' => (string) ($post['redis_select'] ?? '0'),
                ]];
            }
            if (isset($post['driver'])) {
                $driver = (string) $post['driver'];
                $dsn = $this->makeDsn($driver, $post);
                if ($dsn !== null) {
                    $database_list = [[
                        'driver' => $driver,
                        'dsn' => $dsn,
                    ]];
                }
            }
        }
        $base = [
            'use_database' => (bool) $this->options['web_installer_use_database'],
            'use_redis' => (bool) $this->options['web_installer_use_redis'],
            'checks' => $this->checkEnv(),
            'controller_resource_prefix' => (string) (App::_()->options['controller_resource_prefix'] ?? ''),
            'redis_can_follow_root' => $this->checkRootHasRedis(),
            'redis_list' => $redis_list,
            'database_can_follow_root' => $this->checkRootHasDatabase(),
            'database_list' => $database_list,
            'drivers' => $this->getEnabledDatabaseDrivers(),
            'installed' => $installed,
            'redis_error_message' => (string) ($exceptions['redis_error_message'] ?? ''),
            'database_error_message' => (string) ($exceptions['database_error_message'] ?? ''),
            'custom_error_message' => (string) ($exceptions['custom_error_message'] ?? ''),
        ];
        return $base;
    }
    protected function checkRootHasRedis(): bool
    {
        if (
            App::Setting('redis_list', null) ||
            App::Setting('redis', null) ||
            App::Root()->options['redis'] ?? false ||
            App::Root()->options['redis_list'] ?? false 
        ) {
            return true;
        }else{
            return false;
        }
    }
    protected function checkRootHasDatabase(): bool
    {
        return !empty(App::Root()->options['database_driver']);
    }
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public function installBusiness(array $post): array
    {
        $exceptions = [];
        $ext_data = [];
        if ($this->options['web_installer_use_redis'] && empty($post['redis_follow_root'])) {
            try {
                $ext_data = array_merge($ext_data, $this->checkRedis($post));
            } catch (\Exception $e) {
                $exceptions['redis_error_message'] = 'Redis connection failed: '.__h($e->getMessage());
            }
        }
        if ($this->options['web_installer_use_database'] && empty($post['database_follow_root'])) {
            try {
                $ext_data = array_merge($ext_data, $this->checkDatabase($post));
            } catch (\Exception $e) {
                $exceptions['database_error_message'] = 'Database connection failed: '.__h($e->getMessage());
            }
        }
        // checkCustom: override hook for extra validation after redis/database checks; throw \Exception on failure.
        try {
            $ext_data = array_merge($ext_data, $this->checkCustom($post));
        } catch (\Exception $e) {
            $exceptions['custom_error_message'] = $e->getMessage();
        }
        if (!empty($exceptions)) {
            // not all checks passed: do not write anything yet
            return ['exceptions' => $exceptions];
        }
        if ($this->options['web_installer_use_database']) {
            try {
                $ext_data = array_merge($ext_data, $this->doSchema($post, $ext_data));
            } catch (\Exception $e) {
                $exceptions['database_error_message'] = $e->getMessage();
            }
        }
        // doCustom: override hook for extra install steps; runs after doSchema.
        try {
            $ext_data = array_merge($ext_data, $this->doCustom($post, $ext_data));
        } catch (\Exception $e) {
            $exceptions['custom_error_message'] = $e->getMessage();
        }
        if (!empty($exceptions)) {
            return ['exceptions' => $exceptions];
        }
        $ext_data['installed'] = date(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_data);
        return ['installed' => true];
    }
    /**
     * Override hook: extra validation after redis/database checks. Throw \Exception on failure.
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function checkCustom(array $post): array
    {
        return [];
    }
    /**
     * Override hook: extra install steps after doSchema. Throw \Exception on failure.
     * @param array<string, mixed> $post
     * @param array<string, mixed> $ext_data
     * @return array<string, mixed>
     */
    protected function doCustom(array $post, array $ext_data = []): array
    {
        return [];
    }
    //////////////////  env
    protected function checkEnv(): array
    {
        $ret = [];
        $ret[] = [version_compare(PHP_VERSION, '7.4.0', '>='), 'PHP version >= 7.4 ('.PHP_VERSION.')'];
        $ret[] = [extension_loaded('PDO'), 'PDO extension'];
        $drivers = [];
        if ($this->options['web_installer_use_database']) {
            $drivers = $this->getEnabledDatabaseDrivers();
            foreach ($drivers as $driver) {
                $ext = 'pdo_'.$driver;
                $ret[] = [extension_loaded($ext), 'PDO driver: '.$driver];
            }
        }
        if ($this->options['web_installer_use_redis']) {
            $ret[] = [extension_loaded('redis'), 'Redis extension'];
        }
        return $ret;
    }
    //////////////////  redis
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function checkRedis(array $post): array
    {
        if (!class_exists(\Redis::class)) {
            throw new \Exception('Redis extension not loaded');
        }
        $config = [
            'host' => (string) ($post['redis_host'] ?? '127.0.0.1'),
            'port' => (string) ($post['redis_port'] ?? '6379'),
            'auth' => (string) ($post['redis_auth'] ?? ''),
            'select' => (string) ($post['redis_select'] ?? '0'),
        ];
        try {
            $redis = new \Redis();
            if (!$redis->connect($config['host'], (int) $config['port'], 3)) {
                throw new \Exception('connect failed');
            }
            if (!empty($config['auth'])) {
                $redis->auth($config['auth']);
            }
            if ('' !== $config['select']) {
                $redis->select((int) $config['select']);
            }
            if (!$redis->ping()) {
                throw new \Exception('ping failed');
            }
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
        return ['redis_list' => [$config]];
    }

    //////////////////  database
    protected function getEnabledDatabaseDrivers(): array
    {
        if(isset($this->drivers)) {
            return $this->drivers;
        }
        $configured = $this->options['web_installer_database_drivers'] ?? [];
        if (is_string($configured)) {
            // support single driver as a plain string, e.g. 'sqlite'
            $configured = [$configured => true];
        }
        $ret = [];       
        foreach ($configured as $driver => $enabled) {
            if ($enabled) {
                $ret[] = $driver;
            }
        }
        $this->drivers = $ret;
        return $ret;
    }
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function checkDatabase(array $post): array
    {
        $driver = (string) ($post['driver'] ?? '');
        if (!in_array($driver, $this->getEnabledDatabaseDrivers(), true)) {
            throw new \Exception('Unsupported driver: '.__h($driver));
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
            throw new \Exception('Driver requires dbname: '.__h($driver));
        }
        $error = $this->testConnection($dsn, $config['username'], $config['password']);
        if ($error !== null) {
            throw new \Exception('Connection failed: '.__h($error));
        }
        $config['dsn'] = $dsn;
        
        return ['database_list' => [$config]];
    }
    protected function makeDsn(string $driver, array $config): ?string
    {
        if ($driver === 'sqlite' || $driver === 'duckdb') {
            return $driver.':'.($config['dbname'] ?: 'database/database.db');
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
        $filename = $driver.'.sql';
        return App::_()->getConfigFile($filename);
    }
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $ext_data
     * @return array<string, mixed>
     */
    protected function doSchema(array $post, array $ext_data = []): array
    {
        $driver = $this->getCurrentDriver($ext_data);
        if ($driver === null) {
            throw new \Exception('No database configured.');
        }
        $schema_file = $this->getSchemaFile($driver);
        
        if (!is_file($schema_file)) {
            throw new \Exception('Schema file not found: '.__h($schema_file));
        }
        $force = !empty($post['force']);
        try {
            $pdo = $this->createPdo($ext_data);
            if ($force) {
                $this->dropAllTables($pdo, $driver);
            }
            $sql = (string) file_get_contents($schema_file);
            $this->executeSql($pdo, $sql);
        } catch (\Throwable $ex) {
            throw new \Exception('Schema error: '.__h($ex->getMessage()));
        }
        return [];
    }
    /**
     * @param array<string, mixed> $ext_data
     */
    protected function getCurrentDriver(array $ext_data = []): ?string
    {
        $list = $ext_data['database_list'] ?? [];
        if (empty($list)) {
            $list = App::_()->options['database_list'] ?? [];
        }
        if (empty($list)) {
            $list = App::Root()->options['database_list'] ?? [];
        }
        if (empty($list)) {
            return null;
        }
        return explode(':', ''.$list[0]['dsn'])[0];
    }
    /**
     * @param array<string, mixed> $ext_data
     */
    protected function createPdo(array $ext_data = []): \PDO
    {
        $list = $ext_data['database_list'] ?? [];
        if (empty($list)) {
            $list = App::_()->options['database_list'];
        }
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
<p>The application is already installed. To reinstall, please remove the <code>installed</code> entry from the ext options data file.</p>
<?php else: ?>
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
<?php if (!empty($redis_error_message)): ?>
<p class="error"><?=__h((string)$redis_error_message)?></p>
<?php endif; ?>
<p><label><input type="checkbox" name="redis_follow_root" value="1" checked data-target="redis-config"> Follow Main Application</label></p>
<div id="redis-config">
<p><label>Host: <input type="text" name="redis_host" value="127.0.0.1"></label></p>
<p><label>Port: <input type="text" name="redis_port" value="6379"></label></p>
<p><label>Auth: <input type="password" name="redis_auth" value=""></label></p>
<p><label>Select: <input type="text" name="redis_select" value="0"></label></p>
</div>
</fieldset>
<?php endif; ?>
<?php if (!empty($use_database)): ?>
<fieldset>
<legend>Database Config</legend>
<?php if (!empty($database_error_message)): ?>
<p class="error"><?=__h((string)$database_error_message)?></p>
<?php endif; ?>
<p><label><input type="checkbox" name="database_follow_root" value="1"<?= empty($database_can_follow_root) ? '' : ' checked' ?> data-target="database-config"<?= empty($database_can_follow_root) ? ' disabled' : '' ?>> Follow Main Application</label></p>
<div id="database-config">
<p><label>Driver: <select name="driver" onchange="toggleDriver(this)">
<?php foreach($drivers as $driver): ?>
    <option value="<?=__h($driver)?>"><?=__h($driver)?></option>
<?php endforeach; ?>
</select></label></p>
<p data-db-file><label>File: <input type="text" name="dbname" value="database/database.db"></label></p>
<p data-db-server><label>Host: <input type="text" name="host" value="127.0.0.1"></label></p>
<p data-db-server><label>Port: <input type="text" name="port" value=""></label></p>
<p data-db-server><label>Database: <input type="text" name="dbname" value=""></label></p>
<p data-db-server><label>Username: <input type="text" name="username" value=""></label></p>
<p data-db-server><label>Password: <input type="password" name="password" value=""></label></p>
</div>
<p><label><input type="checkbox" name="force" value="1"> Force reinstall (drop existing tables)</label></p>
</fieldset>
<?php endif; ?>
<fieldset>
<legend>Customer Setting</legend>
<?php if (!empty($custom_error_message)): ?>
<p class="error"><?=__h((string)$custom_error_message)?></p>
<?php endif; ?>
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
function toggleFollowRoot(cb) {
    var el = document.getElementById(cb.getAttribute('data-target'));
    if (el) { el.style.display = cb.checked ? 'none' : ''; }
}
function toggleDriver(sel) {
    var file = (sel.value === 'sqlite' || sel.value === 'duckdb');
    toggleRows(document.querySelectorAll('[data-db-file]'), file);
    toggleRows(document.querySelectorAll('[data-db-server]'), !file);
}
var cbs = document.querySelectorAll('input[type="checkbox"][data-target]');
for (var i = 0; i < cbs.length; i++) {
    toggleFollowRoot(cbs[i]);
    cbs[i].onchange = function() { toggleFollowRoot(this); };
}
var __driver = document.querySelector('[name="driver"]');
if (__driver) { toggleDriver(__driver); }
</script>
<?php endif; ?>
</body></html>
<?php
    }
}
