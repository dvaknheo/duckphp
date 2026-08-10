<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;
use DuckPhp\Core\SuperGlobal;

class RouteHookWebInstaller extends ComponentBase
{
    public $options = [
        'web_installer_use_database' => true,
        'web_installer_use_redis' => true,
        'web_installer_database_drivers' => ['sqlite' => true, 'pgsql' => true, 'duckdb' => false],
        'web_installer_view' => '',
        'web_installer_force' => false,
        'web_installer_check_custom_callback' => null,
        'web_installer_do_custom_callback' => null,
        'web_installer_render_custom_callback' => null,
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
    protected function getInstallPath(): string
    {
        return (string) (App::_()->options['url_install'] ?? 'install');
    }
    public function _Hook(string $path_info): bool
    {
        if (__url($this->getInstallPath()) !== __url($path_info)) {
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
            return;
        }
        $post = SuperGlobal::_()->_POST();
        $post = is_array($post) ? $post : [];
        $exceptions = [];
        $installed = false;
        if (!empty($post)) {
            $ret = $this->installBusiness($post);
            $exceptions = $ret['exceptions'] ?? [];
            $installed = !empty($ret['success']);
        }
        $data = $this->buildPageData($post, $exceptions, $installed);

        if ($this->options['web_installer_view']) {
            View::_()->_Show($data, $this->options['web_installer_view']);
        } else {
            $this->show($data);
        }
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
        if (empty($post)) {
            // no POST: build sample default post data so the view renders the default form
            $drivers = $this->getEnabledDatabaseDrivers();
            $post = [
                'driver' => $drivers[0] ?? 'sqlite',
                'database' => ['file' => 'database/database.db', 'host' => '127.0.0.1', 'port' => '', 'dbname' => '', 'username' => '', 'password' => ''],
                'redis' => ['host' => '127.0.0.1', 'port' => '6379', 'auth' => '', 'select' => '0'],
            ];
        }
        $base = [
            'use_database' => (bool) $this->options['web_installer_use_database'],
            'use_redis' => (bool) $this->options['web_installer_use_redis'],
            'checks' => $this->checkEnv(),
            'controller_resource_prefix' => (string) (App::_()->options['controller_resource_prefix'] ?? ''),
            'redis_can_follow_root' => $this->checkRootHasRedis(),
            'database_can_follow_root' => $this->checkRootHasDatabase(),
            'drivers' => $this->getEnabledDatabaseDrivers(),
            'installed' => $installed,
            'redis_error_message' => (string) ($exceptions['redis_error_message'] ?? ''),
            'database_error_message' => (string) ($exceptions['database_error_message'] ?? ''),
            'custom_error_message' => (string) ($exceptions['custom_error_message'] ?? ''),
            'custom_html' => $this->renderCustom($post),
            'post' => $post,
        ];
        return $base;
    }
    /**
     * @param array<string, mixed> $post
     * @return array<int, array<string, string>>
     */
    /**
     * Override hook: render custom setting block (Customer Setting).
     * Return HTML string, or '' to hide the Customer Setting section.
     * @param array<string, mixed> $post filtered POST input (for echo-back on failure)
     * @return string
     */
    protected function renderCustom(array $post): string
    {
        if ($this->options['web_installer_render_custom_callback']) {
            return (string) call_user_func($this->options['web_installer_render_custom_callback'], $post);
        }
        return '';
    }
    protected function checkRootHasRedis(): bool
    {
        // Note: ?? has lower precedence than ||; wrap each array access in parens so ?? guards it.
        return (bool) (
            App::Setting('redis_list', null) ||
            App::Setting('redis', null) ||
            (App::Root()->options['redis'] ?? false) ||
            (App::Root()->options['redis_list'] ?? false)
        );
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

        try {
            $ext_data = array_merge($ext_data, $this->checkRedis($post));
        } catch (\Exception $e) {
            $exceptions['redis_error_message'] = 'Redis connection failed: ' . __h($e->getMessage());
        }
        try {
            $ext_data = array_merge($ext_data, $this->checkDatabase($post));
        } catch (\Exception $e) {
            $exceptions['database_error_message'] = 'Database connection failed: ' . __h($e->getMessage());
        }
        // checkCustom: override hook for extra validation after redis/database checks; throw \Exception on failure.
        try {
            $ext_data = array_merge($ext_data, $this->checkCustom($post));
        } catch (\Exception $e) {
            $exceptions['custom_error_message'] = $e->getMessage();
        }
        if (!empty($exceptions)) {
            // not all checks passed: do not write anything yet
            return ['success' => false, 'exceptions' => $exceptions];
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
            return ['success' => false, 'exceptions' => $exceptions];
        }
        $ext_data['installed'] = date(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_data);
        return ['success' => true, 'exceptions' => []];
    }
    /**
     * Override hook: extra validation after redis/database checks. Throw \Exception on failure.
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    protected function checkCustom(array $post): array
    {
        if ($this->options['web_installer_check_custom_callback']) {
            return (array) call_user_func($this->options['web_installer_check_custom_callback'], $post);
        }
        return [];
    }
    /**
     * Override hook: extra install steps after doSchema. Throw \Exception on failure.
     * Can be replaced by option 'web_installer_do_custom_callback'.
     * @param array<string, mixed> $post
     * @param array<string, mixed> $ext_data
     * @return array<string, mixed>
     */
    protected function doCustom(array $post, array $ext_data = []): array
    {
        if ($this->options['web_installer_do_custom_callback']) {
            return (array) call_user_func($this->options['web_installer_do_custom_callback'], $post, $ext_data);
        }
        return [];
    }
    //////////////////  env
    protected function checkEnv(): array
    {
        $ret = [];
        $ret[] = [version_compare(PHP_VERSION, '7.4.0', '>='), 'PHP version >= 7.4 (' . PHP_VERSION . ')'];
        $ret[] = [extension_loaded('PDO'), 'PDO extension'];
        $drivers = [];
        if ($this->options['web_installer_use_database']) {
            $drivers = $this->getEnabledDatabaseDrivers();
            foreach ($drivers as $driver) {
                $ext = 'pdo_' . $driver;
                $ret[] = [extension_loaded($ext), 'PDO driver: ' . $driver];
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
        if (!($this->options['web_installer_use_redis'] && empty($post['redis_follow_root']))) {
            return [];
        }

        $config = (array) ($post['redis'] ?? []);
        $config = [
            'host' => (string) ($config['host'] ?? '127.0.0.1'),
            'port' => (string) ($config['port'] ?? '6379'),
            'auth' => (string) ($config['auth'] ?? ''),
            'select' => (string) ($config['select'] ?? '0'),
        ];
        try {
            // test connection through a fresh RedisManager instance (do not touch the global singleton)
            $options = [
                'redis_list' => [$config],
                'redis_list_reload_by_setting' => false,
            ];
            $redis = (new RedisManager())->init($options, App::_())->getServer(0);
            if (!$redis->ping()) {
                throw new \Exception('ping failed');
            }
        } catch (\Throwable $e) {
            throw new \Exception($config['host'] . ':' . $config['port'] . ' ' . $e->getMessage());
        }
        $ret = ['redis_list' => [$config]];
        if (!App::_()->isRoot()) {
            // child app uses its own local redis config
            $ret['local_redis'] = true;
        }
        return $ret;
    }

    //////////////////  database
    protected function getEnabledDatabaseDrivers(): array
    {
        if (isset($this->drivers)) {
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
        if (!($this->options['web_installer_use_database'] && empty($post['database_follow_root']))) {
            return [];
        }
        $driver = (string) ($post['driver'] ?? '');
        if (!in_array($driver, $this->getEnabledDatabaseDrivers(), true)) {
            throw new \Exception('Unsupported driver: ' . __h($driver));
        }
        $config = (array) ($post['database'] ?? []);
        $config = [
            'file' => (string) ($config['file'] ?? ''),
            'host' => (string) ($config['host'] ?? '127.0.0.1'),
            'port' => (string) ($config['port'] ?? ''),
            'dbname' => (string) ($config['dbname'] ?? ''),
            'username' => (string) ($config['username'] ?? ''),
            'password' => (string) ($config['password'] ?? ''),
        ];
        $dsn = $this->makeDsn($driver, $config);
        if ($dsn === null) {
            throw new \Exception('Driver requires dbname: ' . __h($driver));
        }

        $database = [];
        $database['dsn'] = $dsn;
        $database['username'] = $config['username'];
        $database['password'] = $config['password'];
        $ret = ['database_list' => [$database]];

        $options = [
            'database_list' => [$database],
            'database_list_reload_by_setting' => false,
        ];
        // test connection through DbManager and leave the global singleton configured
        // (doSchema below reuses DbManager::_() with this connection)
        DbManager::_(new DbManager())->init($options, App::_())->_Db()->execute('select 1');

        if (!App::_()->isRoot()) {
            // child app uses its own local database config
            $ret['local_database'] = true;
        }
        return $ret;
    }
    protected function makeDsn(string $driver, array $config): ?string
    {
        if ($driver === 'sqlite' || $driver === 'duckdb') {
            $file = $config['file'] ?? $config['dbname'] ?? '';
            return $driver . ':' . ($file ?: 'database/database.db');
        }
        if (empty($config['dbname'])) {
            return null;
        }
        $dsn = $driver . ':host=' . $config['host'] . ';dbname=' . $config['dbname'];
        if (!empty($config['port'])) {
            $dsn .= ';port=' . $config['port'];
        }
        return $dsn;
    }
    //////////////////  schema

    /**
     * Locate schema sql file: {driver}{.suffix}.sql in the app config dir.
     * suffix '' -> {driver}.sql (create), 'clean' -> {driver}.clean.sql (drop), 'data' -> {driver}.data.sql (seed data).
     */
    protected function getSchemaSqlFile(string $driver, string $suffix = ''): string
    {
        $filename = $driver . ($suffix === '' ? '' : '.' . $suffix) . '.sql';
        return App::_()->getConfigFile($filename);
    }
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $ext_data
     * @return array<string, mixed>
     */
    protected function doSchema(array $post, array $ext_data = []): array
    {
        $driver = $this->getCurrentDriver();
        if ($driver === null) {
            //@codeCoverageIgnoreStart
            throw new \Exception('No database configured.');
            //@codeCoverageIgnoreEnd
        }
        $force = !empty($post['force']);
        try {
            // DbManager::_() is already configured by checkDatabase; reuse its connection
            $db = DbManager::_()->_Db();
            if ($force) {
                // force reinstall: run clean script first if present
                $clean_file = $this->getSchemaSqlFile($driver, 'clean');
                if (is_file($clean_file)) {
                    $this->executeSqlFile($db, $clean_file);
                }
            }
            $schema_file = $this->getSchemaSqlFile($driver);
            if (!is_file($schema_file)) {
                throw new \Exception('Schema file not found: ' . __h($schema_file));
            }
            $this->executeSqlFile($db, $schema_file);
            // seed data script if present
            $data_file = $this->getSchemaSqlFile($driver, 'data');
            if (is_file($data_file)) {
                $this->executeSqlFile($db, $data_file);
            }
        } catch (\Throwable $ex) {
            throw new \Exception('Schema error: ' . __h($ex->getMessage()));
        }
        return [];
    }
    /**
     * Execute a schema sql file, replacing the {prefix} placeholder with the app table_prefix.
     */
    protected function executeSqlFile(\DuckPhp\Db\Db $db, string $file): void
    {
        $sql = (string) file_get_contents($file);
        $prefix = (string) (App::_()->options['table_prefix'] ?? '');
        $sql = str_replace('{prefix}', $prefix, $sql);
        $this->executeSql($db, $sql);
    }
    /**
     * @param array<string, mixed> $ext_data
     */
    protected function getCurrentDriver(): ?string
    {
        $driver = DbManager::_()->getDatabaseDriver();
        return $driver === '' ? null : $driver;
    }
    protected function executeSql(\DuckPhp\Db\Db $db, string $sql): void
    {
        // split into statements for all drivers: PDO mysql disables multi-statement by default,
        // pgsql does not support multi-statement exec, sqlite is safer split as well.
        foreach (preg_split('/;\s*(\n|$)/', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $db->execute($statement);
            }
        }
    }

    /**
     * Show page: render all install info in a single page by built-in view.
     * @param array<string, mixed> $data
     */
    protected function show(array $data)
    {
        extract($data);
        $title = !empty($installed) ? 'Already Installed' : 'DuckPhp Web Installer';
        include __DIR__.'/RouteHookWebInstallerView.php';
    }
}
