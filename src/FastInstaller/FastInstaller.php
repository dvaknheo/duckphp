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
    /**
     * Install. power by DuckPhp\Foundation\FastInstallerTrait
     */
    public function command_install()
    {
        return $this->doCommandInstall();
    }
    /**
     * override me to add a child app.
     */
    public function command_require()
    {
        $args = Console::_()->getCliParameters();
        $app = $args['--'][1] ?? null;
        App::Phase($app);
        return $this->doCommandInstall();
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
    public function doCommandUpdate()
    {
        EventManager::FireEvent([App::Phase(), 'OnInstallUpdate']);
    }
    public function doCommandRemove()
    {
        EventManager::FireEvent([App::Phase(), 'OnInstallRemove']);
    }
    public function doInstall()
    {
        $force = $this->args['force'] ?? false;
        //////////////////////////
        $install_level = App::Root()->options['installing_data']['install_level'] ?? 0;
        echo ($install_level <= 0) ? "use --help for more info.\n" : '';
        echo str_repeat("\t", $install_level)."\e[32;7mInstalling (".get_class(App::Current())."):\033[0m\n";
    
        DatabaseInstaller::_()->install($force);
        RedisInstaller::_()->install($force);
        
        //////[[[[
        $app_options = App::Current()->options;
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        
        $validators = $app_options['install_input_validators'] ?? [];
        
        $default_options = $app_options['install_options'] ?? [];
        $default_options = array_replace_recursive($app_options, $ext_options, $default_options);

        $desc = $this->adjustPrompt($desc, $default_options, $ext_options, $app_options);
        $input_options = Console::_()->readLines($default_options, $desc, $validators);

        $flag = $this->doInstallAction($input_options, $ext_options, $app_options);
        if (!$flag) {
            echo "\e[32;3mInstalled App (".get_class(App::Current()).") FAILED!;\033[0m\n";
            return;
        }
        $ext_options = array_replace_recursive($ext_options, $input_options);
        $app_options = array_replace_recursive($app_options, $ext_options);
        
        App::Current()->options = $app_options;
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
        
        EventManager::FireEvent([App::Phase(), 'OnInstall'], $input_options, $ext_options, $app_options);
        
        ///////////////////////////
        if (!($this->args['skip_children'] ?? false)) {
            EventManager::FireEvent([App::Phase(), 'OnBeforeChildrenInstall']);
            $this->installChildren();
        }
        EventManager::FireEvent([App::Phase(), 'OnInstalled']);

        $this->saveInstalledFlag();
        echo "\e[32;3mInstalled App (".get_class(App::Current()).");\033[0m\n";
        return;
    }
    protected function adjustPrompt($desc, $default_options, $ext_options, $app_options)
    {
        $desc = $app_options['install_input_desc'] ?? '--';
        $prefix = '';
        if (!(App::Current()->isRoot())) {
            $prefix = "
--
url prefix: [{controller_url_prefix}]
resource prefix: [{controller_resource_prefix}]
";
        }
        
        foreach ($default_options as $key) {
            $prefix = str_replace('{'.$key.'}', $default_options[$key], $prefix);
        }
        $desc = $prefix.$desc;
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
            $true_app = get_class($app::_());
            $last_phase = App::Phase($true_app);
            $cli_namespace = App::Current()->options['cli_command_prefix'] ?? App::Current()->options['namespace'];
            $group = Console::_()->options['cli_command_group'][$cli_namespace] ?? [];
            list($class, $method) = Console::_()->getCallback($group, 'install');
            try {
                $ret = call_user_func([$class,$method]);
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
    protected function doInstallAction($input_options = [], $ext_options = [], $app_options = [])
    {
        if (!($this->args['skip_sql'] ?? false)) {
            SqlDumper::_()->install($this->args['force'] ?? false);
        }
        if (!($this->args['skip_resource'] ?? false)) {
            $info = '';
            RouteHookResource::_()->cloneResource(false, $info);
            echo $info;
        }
        if (!method_exists(App::Current(), 'callbackForFastInstallerDoInstall')) {
            return true;
        }
        $flag = App::Current()->callbackForFastInstallerDoInstall($input_options, $ext_options, $app_options);
        return $flag;
    }
    protected function saveInstalledFlag()
    {
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        $ext_options['install'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
    }
    //////////////////
}
