<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\RouteHookResource;
use DuckPhp\Component\SqlDumper;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;
use DuckPhp\Ext\DatabaseInstaller;
use DuckPhp\Ext\RedisInstaller;

class FastInstaller extends ComponentBase
{
    public $options = [
    ];
    protected $args = [];
    ///////////////
    protected function configDatabase($force = false)
    {
        $install_need_database = $this->reduce_apps(App::Current(), function ($app) {
            return isset($app->options['install_need_database']) ? $app->options['install_need_database'] : true;
        });
        if (!$install_need_database) {
            return;
        }
        if (!$force && (App::Root()->options['install_database_configed'] ?? false)) {
            return;
        }
        echo "need database, config now: ";
        DatabaseInstaller::_()->callResetDatabase($force);
        App::Root()->options['database_configed'] = true;
    }
    protected function configRedis($force = false)
    {
        $install_need_redis = $this->reduce_apps(App::Current(), function ($app) {
            return isset($app->options['install_need_redis']) ? $app->options['install_need_redis'] : false;
        });
        if (!$install_need_redis) {
            return;
        }
        if (!$force && App::Root()->options['install_need_redis_configed'] ?? false) {
            return;
        }
        echo "need redis, config now   : ";
        RedisInstaller::_()->callResetDatabase($force);
        App::Root()->options['redis_configed'] = true;
    }
    private function initComponents()
    {
        $classes = [
            ExtOptionsLoader::class,
            DatabaseInstaller::class,
            RedisInstaller::class,
            SqlDumper::class,
        ];
        foreach ($classes as $class) {
            if (!$class::_()->isInited()) {
                $class::_()->init(App::Current()->options, App::Current());
            }
        }
    }
    protected function showHelp($app_options = [], $input_options = [])
    {
        echo "
--help      show this help.
--configure config such as database, redis only ,--force
--dry       show options ,do no action. not with childrens.
--force     force install
--dump-sql  dump sql , no with childrens.
and more ...\n";
    }
    public function doCommandInstall()
    {
        $this->initComponents();
        $args = Console::_()->getCliParameters();
        echo "use --help for more info.\n";
        if ($args['help'] ?? false) {
            $this->showHelp();
            return;
        }
        if ($args['dump_sql'] ?? false) {
            //$this->dump_sql;
            return;
        }
        $this->doInstall();
    }
    public function doInstall()
    {
        $this->args = Console::_()->getCliParameters();
        $is_root = App::Current()->isRoot();
        $app_options = App::Current()->options;
        
        $this->configDatabase($this->args['force'] ?? false);
        $this->configRedis($this->args['force'] ?? false);
        
        if ($this->args['--configure']) {
            return;
        }
        //////////////////////////

        // inputs
        $desc = $app_options['install_input_desc'] ?? '';
        $validators = $app_options['install_input_validators'] ?? [];
        $default_options = $app_options['install_options'] ?? [];
        
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        $default_options = array_replace_recursive($app_options, $ext_options, $default_options);
        
        
        $desc = "Installing App (".get_class(App::Current())."):\n";
        $desc .= "----\n".$desc."\n----\n";
        if (!$is_root) {
            // 'controller_url_prefix' => 'app/admin/'
            // 'controller_resource_prefix' => 'res/'
            //"route prefix: [{controller_url_prefix}]" // if parent is solid ,so rolid
            // resource prefix('./' will change to '') ['{controller_resource_prefix}'];
        }
        
        $input_options = Console::_()->readLines($default_options, $desc, $validators);
        
        $ext_options = array_replace_recursive($ext_options, $input_options);
        $app_options = array_replace_recursive($app_options, $ext_options);
        App::Current()->options = $app_options;
        
        
        if ($this->args['dry']) {
            $this->showHelp($input_options, $ext_options);
            return;
        }
        $this->doInstallMore($app_options, $input_options);
        //FIRE AN event
        ////]]]]
        
        $ext_options['install'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
        
        if (!empty($app_options['app'])) {
            echo "\nInstall child apps\n----------------\n";
        }
        ///////////////////////////
        foreach ($app_options['app'] as $app => $options) {
            $last_phase = App::Phase($app);
            try {
                $app::_()->command_install();
            } catch (\Exception $ex) {
                $msg = $ex->getMessage();
                echo "\Install failed: $msg \n";
            }
            App::Phase($last_phase);
        }
        
        $this->onInstall();
        echo "\n---- Install Done.\n";
        return;
    }
    protected function doInstallMore($app_options = [], $input_options = [])
    {
        if (!($this->args['skip_sql'] ?? false)) {
            SqlDumper::_()->install();
        }
        if (!($this->args['skip_resource'] ?? false)) {
            $info = '';
            RouteHookResource::_()->cloneResource(false, $info);
        }
    }
    protected function onInstall()
    {
        //for override;
    }
    //////////////////
    protected function reduce_apps($object, $callback)
    {
        $ret = $callback($object);
        if ($ret) {
            return true;
        }
        foreach ($object->options['app'] as $app => $options) {
            $args = $this->reduce_apps($app::_(), $callback);
            if ($ret) {
                return true;
            }
        }
        return false;
    }
}
