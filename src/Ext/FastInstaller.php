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
    public function doConfig($force = false)
    {
        $args = ['install_need_database' => false,'install_need_redis' => false];
        
        $args = $this->rec_apps(App::Current(), function ($app, $args) {
            $install_need_database = isset($app->options['install_need_database']) ? $app->options['install_need_database'] : true;
            $install_need_redis = isset($app->options['install_need_redis']) ? $app->options['install_need_redis'] : true;
            $args['install_need_database'] = $args['install_need_database'] || $install_need_database;
            $args['install_need_redis'] = $args['install_need_redis'] || $install_need_redis;
            
            return $args;
        }, $args);
        if ($args['install_need_database']) {
            echo "need database, config now: ";
            DatabaseInstaller::_()->callResetDatabase($force);
        }
        if ($args['install_need_redis']) {
            echo "need redis, config now   : ";
            RedisInstaller::_()->callResetRedis($force);
        }
        echo "config database, redis done.";
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
        echo " --help , --config, --force --dump-sql  and more ...\n";
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
        $this->initComponents();
        
        //////////////////////////
        if ($is_root) {
            $this->doConfig($force = false);
        }
        //////////////////////////
        
        echo "Installing App (".get_class(App::Current())."):\n";
        
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        $desc = $app_options['install_input_desc'] ?? '';
        $desc = "----\n".$desc."\n----\n";
        $default_options = $app_options['install_options'] ?? [];
        $default_options = array_replace_recursive($app_options, $ext_options, $default_options);
        $input_options = Console::_()->readLines($default_options, $desc);
        
        $ext_options = array_replace_recursive($ext_options, $input_options);
        $app_options = array_replace_recursive($app_options, $ext_options);
        
        //SqlDumper::_()->run();
        //RouteHookrewrite::_()->cloneResource($force,$info);
        
        //$this->doInstallMore($ext_options);
        $ext_options['install'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
        
        if (!empty($app_options['app'])) {
            echo "\nInstall child apps\n----------------\n";
        }
        ///////////////////////////
        foreach ($app_options['app'] as $app => $options) {
            $last_phase = App::Phase($app);
            try {
                $app::_()->doInstall($force); //configed?,child
            } catch (\Exception $ex) {
                $msg = $ex->getErrorMesage();
                echo "\Install failed: $msg \n";
            }
            App::Phase($last_phase);
        }
        
        $this->onInstall();
        if ($is_root) {
            echo "\---- Install All Done.\n";
        }
        return;
    }
    protected function onInstall()
    {
        //for override;
    }
    //////////////////
    protected function rec_apps($object, $callback, $args)
    {
        $args = $callback($object, $args);
        foreach ($object->options['app'] as $app => $options) {
            $args = $this->rec_apps($app::_(), $callback, $args);
        }
        return $args;
    }
}
