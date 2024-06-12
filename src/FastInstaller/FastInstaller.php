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
    protected $is_failed = false;
    protected $current_input_options = [];
    ///////////////
    /**
     * Install. power by DuckPhp\Foundation\FastInstallerTrait
     */
    public function command_install()
    {
        return $this->doCommandInstall();
    }
    public function command_dumpsql()
    {
        $this->initComponents();
        SqlDumper::_()->dump();
        echo "dumpsql done. see the file `config/\$database_driver.sql` .\n";
        return;
    }
    /**
     * override me to add a child app. require aa/app --url-prefix= aa-bb
     */
    public function command_require()
    {
        $args = Console::_()->getCliParameters();
        $app = $args['--'][1] ?? null;
        /*
        if (!App::IsRoot()) {
            echo "only use by root.\n";
            return;
        }
        $app = str_replace('/','\\',$app);
        if(!is_a($app, App::class){
            echo "must be an app\n";
            return;
        }
        if(!isset(App::_()->options['app'][$app]) {
            if(!(App::_()->options['allow_require_ext_app']??false)){
                echo "You Need  turn on options `allow_require_ext_app`";
                return;
            }
            $name = (new $app)->options['namespace'];
            $name = str_replace('\\','/',$name);
            $name = trim(strtolower(preg_replace('/([A-Z])/', "-$1", $name),'-');
            //Console::_()->readLines('install to url [{url}]',)
            //ExtOptionsLoader::()-
            //$ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        }
        // extoption::  --[DuckAdmin/] DuckAdmin duck-admin/ ? // save ,do install
        //*/
        App::Phase($app);
        
        return FastInstaller::_()->doCommandInstall();
    }
    /**
     * override me to update
     */
    public function command_update()
    {
        return $this->doCommandUpdate();
    }
    /**
     * override me to remove a child app.
     */
    public function command_remove()
    {
        return $this->doCommandRemove();
    }
    
    //////////////////
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
--dry               show options, do no action. not with childrens.
--force             force install.
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
        
        if ($args['help'] ?? false) {
            $this->showHelp();
            return;
        }
        $this->doInstall();
    }
    public function doCommandUpdate()
    {
        EventManager::FireEvent([App::Phase(), 'OnInstallUpdate']);
    }
    public function doCommandRemove()
    {
        EventManager::FireEvent([App::Phase(), 'OnInstallRemove']);
    }
    public function forceFail()
    {
        $this->is_failed = true;
    }
    public function getCurrentInput()
    {
        return $this->current_input_options;
    }
    public function doInstall()
    {
        $force = $this->args['force'] ?? false;
        //////////////////////////
        $install_level = App::Root()->options['installing_data']['install_level'] ?? 0;
        //echo ($install_level <= 0) ? "use --help for more info.\n" : '';
        echo str_repeat("\t", $install_level)."\e[32;7mInstalling (".get_class(App::Current())."):\033[0m more info by --help .\n";
        
        if (method_exists(App::Current(), 'onPreInstall')) {
            App::Current()->onPreInstall();
        }
        if (!($this->args['skip_sql'] ?? false)) {
            DatabaseInstaller::_()->install($force);
        }
        RedisInstaller::_()->install($force);
        
        //////[[[[
        $app_options = App::Current()->options;
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        
        $validators = $app_options['install_input_validators'] ?? [];
        
        $default_options = $app_options['install_options'] ?? [];
        $default_options = array_replace_recursive($app_options, $ext_options, $default_options);

        $desc = $this->adjustPrompt($app_options['install_input_desc'] ?? '', $default_options, $ext_options, $app_options);
        $input_options = Console::_()->readLines($default_options, $desc, $validators);
        $this->current_input_options = $input_options;
        
        $flag = $this->doInstallAction();
        if (method_exists(App::Current(), 'onInstall')) {
            App::Current()->onInstall();
        }
        EventManager::FireEvent([App::Phase(), 'onInstall'], $input_options, $ext_options, $app_options);
        if ($this->is_failed) {
            echo "\e[32;3mInstalled App (".get_class(App::Current()).") FAILED!;\033[0m\n";
            return;
        }
        
        $ext_options = array_replace_recursive($ext_options, $input_options);
        $app_options = array_replace_recursive($app_options, $ext_options);
        App::Current()->options = $app_options;
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
        
        ///////////////////////////
        if (!($this->args['skip_children'] ?? false)) {
            EventManager::FireEvent([App::Phase(), 'onBeforeChildrenInstall']);
            $this->installChildren();
        }
        $this->saveInstalledFlag();
        EventManager::FireEvent([App::Phase(), 'onInstalled']);
        if (method_exists(App::Current(), 'onInstalled')) {
            App::Current()->onInstalled();
        }
        echo "\e[32;3mInstalled App (".get_class(App::Current()).");\033[0m\n";
        return;
    }
    protected function adjustPrompt($desc, $default_options, $ext_options, $app_options)
    {
        $desc = $app_options['install_input_desc'] ?? '--';
        $prefix = '';
        if (!(App::IsRoot())) {
            $prefix = "
--
url prefix: [{controller_url_prefix}]
resource prefix: [{controller_resource_prefix}]
";
        }
        $desc = $prefix.$desc;
        
        $desc = str_replace('{controller_url_prefix}', App::Current()->options['controller_url_prefix'] ?? '', $desc);
        $desc = str_replace('{controller_resource_prefix}', App::Current()->options['controller_resource_prefix'] ?? '', $desc);
        
        //foreach ($app_options as $key => $value) {
        //    $desc = str_replace('{'.$key.'}', is_scalar($app_options[$key])?$app_options[$key]:'', $desc);
        //}
        return  $desc;
    }
    protected function installChildren()
    {
        $current_phase = App::Phase();
        
        $app_options = App::Current()->options;
        if (!empty($app_options['app'])) {
            $install_level = App::Root()->options['installing_data']['install_level'] ?? 0;
            App::Root()->options['installing_data']['install_level'] = $install_level + 1;
            echo "\nInstall child apps [[[[[[[[\n\n";
        } else {
            return;
        }
        foreach ($app_options['app'] as $app => $options) {
            $last_phase = App::Phase($app::_()->getOverridingClass());
            $cli_namespace = App::Current()->options['cli_command_prefix'] ?? App::Current()->options['namespace'];
            $group = Console::_()->options['cli_command_group'][$cli_namespace] ?? [];
            list($class, $method) = Console::_()->getCallback($group, 'install');
            try {
                if (is_callable([$class,$method])) {
                    $ret = call_user_func([$class,$method]); /** @phpstan-ignore-line */
                }
            } catch (\Exception $ex) {
                $msg = $ex->getMessage();
                echo "\Install failed: $msg \n";
            }
            App::Phase($last_phase);
        }
        if (!empty($app_options['app'])) {
            echo "\n]]]]]]]] Installed child apps\n";
        }
        App::Phase($current_phase);
    }
    protected function doInstallAction()
    {
        if (!($this->args['skip_sql'] ?? false)) {
            SqlDumper::_()->install($this->args['force'] ?? false);
        }
        if (!($this->args['skip_resource'] ?? false)) {
            $info = '';
            RouteHookResource::_()->cloneResource(false, $info);
            if ($this->args['verbose'] ?? false) {
                echo $info;
            }
        }
        return true;
    }
    protected function saveInstalledFlag()
    {
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        $ext_options['installed'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
    }
    //////////////////
}
