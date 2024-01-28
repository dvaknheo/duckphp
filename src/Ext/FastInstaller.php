<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Component\ExtOptionsLoader;
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
    ///////////////
    protected function configDatabase($force = false)
    {
        $install_need_database = $this->reduce_apps(App::Current(), function ($app) {
            return isset($app->options['install_need_database']) ? $app->options['install_need_database'] : true;
        });
        if (!$install_need_database) {
            return;
        }
        if (!$force && App::Root()->options['install_database_configed']??false) {
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
        if (!$force && App::Root()->options['install_need_redis_configed']??false) {
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
    public function doDebug()
    {
        $args = Console::_()->getCliParameters();
        $is_off = $args['off'] ?? false;
        $is_debug = !$is_off;
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        $ext_options['is_debug'] = $is_debug;
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
        App::Current()->options['is_debug'] = $is_debug;
        if ($is_debug) {
            echo "Debug mode has turn on. us --off to off\n";
        } else {
            echo "Debug mode has turn off.\n";
        }
    }
    protected function showHelp()
    {
        echo " --help , --dry  --force --dump-sql  and more ...\n";
    }
    public function doCommandInstall()
    {
        $args = Console::_()->getCliParameters();
        echo "use --help for more info.\n";
        if($args['help']??false){
            $this->showHelp();
        }
        
        //$this->doInstall();
    }
    public function doInstall($force = false)
    {
        $is_root  = App::Current()->isRoot();
        $app_options = App::Current()->options;
        
        $this->configDatabase();
        $this->configRedis();
        
        
        //////////////////////////
        $desc = $app_options['install_input_desc'] ?? '';
        $default_options = $app_options['install_options'] ?? [];
        
        echo "Installing App (".get_class(App::Current())."):\n";
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        //validaore
        
        $desc = "----\n".$desc."\n----\n";
        $default_options = array_replace_recursive($app_options, $ext_options, $default_options);
        $input_options = Console::_()->readLines($default_options, $desc);
        
        $ext_options = array_replace_recursive($ext_options, $input_options);
        $app_options = array_replace_recursive($app_options, $ext_options);
        
        
        ////[[[[
        // init_app_options,ext_options, $input_options,$app_options
        //SqlDumper::_()->run();
        //RouteHookrewrite::_()->cloneResource($force,$info);
        
        ////]]]]
        
        
        $ext_options['install'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
        App::Current()->options = $app_options;
        if (!empty($app_options['app'])) {
            echo "\nInstall child apps\n----------------\n";
        }
        ///////////////////////////
        foreach ($app_options['app'] as $app => $options) {
            $last_phase = App::Phase($app);
            try {
                $app::_()->command_install();
            } catch (\Exception $ex) {
                $msg = $ex->getErrorMesage();
                echo "\Install failed: $msg \n";
            }
            App::Phase($last_phase);
        }
        
        $this->onInstall();
        echo "\n---- Install Done.\n";
        return;
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
