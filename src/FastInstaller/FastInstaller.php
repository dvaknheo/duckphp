<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\RouteHookResource;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;
use DuckPhp\Core\EventManager;
use DuckPhp\FastInstaller\DatabaseInstaller;
use DuckPhp\FastInstaller\RedisInstaller;
use DuckPhp\FastInstaller\SqlDumper;

class FastInstaller extends ComponentBase
{
    public $options = [
        //install_input_validators
        //install_need_redis
        //install_options
        //install_input_desc
    ];
    protected $args = [];
    ///////////////

    protected function initComponents()
    {
        $this->args = Console::_()->getCliParameters();
        $classes = [
            static::class,
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
--help              show this help.
--configure         config such as database, redis only ,--force
--dry               show options ,do no action. not with childrens.
--force             force install.
--dump-sql          not install, just dump sql for install, no with childrens.
--skip-sql          skip install sql
--skip-resource     skip copy resource
--skip-children     skip child app

and more ...\n";
    }
    public function doCommandInstall()
    {
        $this->initComponents();
        
        App::Root()->options['installing_data'] = App::Root()->options['installing_data'] ?? [];
       
        $args = $this->args;
        
        echo "use --help for more info.\n";
        if ($args['help'] ?? false) {
            $this->showHelp();
            return;
        }
        if ($args['dump_sql'] ?? false) {
            SqlDumper::_()->dump();
            var_dump('done: dumpsql');
            return;
        }
        $this->doInstall();
    }
    public function doCommandRequire()
    {
        EventManager::FireEvent([App::Phase(), 'OnInstallRequire']);
    }
    public function doCommandRemove()
    {
        EventManager::FireEvent([App::Phase(), 'OnInstallRemove']);
    }
    protected function doGlobalConfigure()
    {
        
    }
    public function doInstall()
    {
        $force = $this->args['force'] ?? false;
        //////////////////////////
        $install_level = App::Root()->options['installing_data']['install_level']??0;
        echo ($install_level<=0) ? "use --help for more info.\n" : '';
        echo str_repeat("\t",$install_level)."\e[32;7mInstalling (".get_class(App::Current())."):\033[0m\n";
    
        DatabaseInstaller::_()->install($force);
        RedisInstaller::_()->install($force);
        
        //////[[[[
        $app_options = App::Current()->options;
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        
        $validators = $app_options['install_input_validators'] ?? [];
        
        $default_options = $app_options['install_options'] ?? [];
        $default_options = array_replace_recursive($app_options, $ext_options, $default_options);
        
        $desc = $app_options['install_input_desc'] ?? '--';
        
        $desc = $this->adjustPrompt($desc, $default_options, $ext_options, $app_options);
        $input_options = Console::_()->readLines($default_options, $desc, $validators);

        $ext_options = array_replace_recursive($ext_options, $input_options);
        $app_options = array_replace_recursive($app_options, $ext_options);
        
        App::Current()->options = $app_options;
        ///////////////

        $this->doInstallAction($input_options, $ext_options, $app_options);
        $ext_options['install'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
        
        //$this->onInstall(); //Oninstall
        EventManager::FireEvent([App::Phase(), 'OnBeforeChildrenInstall']);
        ///////////////////////////
        if (!($this->args['skip_children'] ?? false)) {
            $this->installChildren();
        }
        EventManager::FireEvent([App::Phase(), 'OnInstalled']);
        echo "\e[32;3mInstalled App (".get_class(App::Current()).");\033[0m\n";
        return;
    }
    protected function adjustPrompt($desc, $default_options, $ext_options, $app_options)
    {
        $prefix ='';
        if (!(App::Current()->isRoot())) {
            $prefix = "
--
url prefix: [{controller_url_prefix}]
resource prefix: [{controller_resource_prefix}]

";
        }
        $prefix = str_replace('{controller_url_prefix}',$default_options['controller_url_prefix'] ,$prefix);
        $prefix = str_replace('{controller_resource_prefix}',$default_options['controller_resource_prefix'] ,$prefix);
        
        $desc = $prefix.$desc;
        return  $desc;
    }
    protected function installChildren()
    {
        $app_options = App::Current()->options;
        if (!empty($app_options['app'])) {
            App::Root()->options['installing_data']['install_level']=$install_level+1;
            echo "\nInstall child apps [[[[[[[[\n\n";
        }
        foreach ($app_options['app'] as $app => $options) {
            $true_app = get_class($app::_());
            $last_phase = App::Phase($true_app);
            //try {
            $command_class= $app->options['cli_command_class']??$true_app;
            $command_class::_()->command_install();
            //} catch (\Exception $ex) {
            //    $msg = $ex->getMessage();
            //    echo "\Install failed: $msg \n";
            //}
            App::Phase($last_phase);
        }
        if (!empty($app_options['app'])) {
            echo "\n]]]]]]]] Installed child apps\n";
        }
    }
    protected function doInstallAction($input_options = [], $ext_options = [], $app_options = [])
    {
        if (!($this->args['skip_sql'] ?? false)) {
            App::Current()->options['is_debug']=true;
            SqlDumper::_()->options['sql_dump_install_drop_old_table'] = true;
            SqlDumper::_()->install();
        }
        if (!($this->args['skip_resource'] ?? false)) {
            $info = '';
            RouteHookResource::_()->cloneResource(false, $info);
            echo $info;
        }
        if ($this->options['on_install']){
            $callback = $this->options['on_install'];
            ($callback)($input_options, $ext_options, $app_options);
        }
        EventManager::FireEvent([App::Phase(), 'OnInstall'],$input_options, $ext_options, $app_options);
        return true;
    }
    //////////////////
    protected function reduce_apps($object, $callback)
    {
        $ret = $callback($object);
        if ($ret) {
            return true;
        }
        foreach ($object->options['app'] as $app => $options) {
            $ret = $this->reduce_apps($app::_(), $callback);
            if ($ret) {
                return true;
            }
        }
        return false;
    }
}
