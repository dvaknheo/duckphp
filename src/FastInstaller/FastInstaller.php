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
        'install_input_validators' => [],
        'install_options' => [],
        'install_input_desc' => '',
        'install_callback' => null,
    ];
    protected $args = [];
    protected $is_failed = false;
    protected $current_input_options = [];
    ///////////////
    /**
     * Install. power by DuckPhp\FastInstaller\FastInstaller
     */
    public function command_install()
    {
        return $this->doCommandInstall();
    }
    /**
     * dump sql power by DuckPhp\FastInstaller\FastInstaller
     */
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
        if (!App::IsRoot()) {
            echo "only use by root.\n";
            return;
        }
        $app = str_replace('/', '\\', $app);
        $object = new $app();
        if (!is_a($object, App::class)) {
            echo "must be an app:[$app]\n";
            return;
        }

        if (!isset(App::_()->options['app'][$app])) {
            if (!(App::_()->options['allow_require_ext_app'] ?? false)) {
                echo "You Need  turn on options `allow_require_ext_app`";
                return;
            }
            $app::_($object)->init([], App::Root());
            
            $desc = "Install to Url prefix: [{controller_url_prefix}]\n";
            $default_options = [];
            
            $default_options['controller_url_prefix'] = $this->getDefaultUrlPrefixX($object->options['namespace']);
            $input_options = Console::_()->readLines($default_options, $desc, []);
            
            $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::_());
            $ext_options['app'][$app] = ['controller_url_prefix' => $input_options['controller_url_prefix']];
            ExtOptionsLoader::_()->saveExtOptions($ext_options, App::_());
            App::_()->options['app'][$app] = ['controller_url_prefix' => $input_options['controller_url_prefix']];
            $object->options['controller_url_prefix'] = $input_options['controller_url_prefix'];
        }
        App::Phase($app);
        return FastInstaller::_()->doCommandInstall();
    }
    protected function getDefaultUrlPrefixX($ns)
    {
        $ns = str_replace('\\', '/', $ns);
        $ns = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $ns), '-')).'/';
        return $ns;
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
    protected function getDefaultUrlPrefix()
    {
        $ns = App::Current()->options['namespace'];
        $ns = str_replace('\\', '/', $ns);
        $ns = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $ns), '-')).'/';
        return $ns;
    }
    protected function changeResource()
    {
        ////[[[[
        $res_options = RouteHookResource::_()->options;
        $source = RouteHookResource::_()->extendFullFile($res_options['path'], $res_options['path_resource'], '', false);
        $source = realpath($source);
        if (!$source) {
            return [];
        }
        ////]]]]
        
        $controller_resource_prefix = App::Current()->options['controller_resource_prefix'] ?? '';
        
        $desc = "Current resource url to visit is [$controller_resource_prefix]\n";
        $desc .= "Change resource url (Y/N)[{sure}]?";
        $sure = Console::_()->readLines(['sure' => 'Y'], $desc);
        if (strtoupper($sure['sure']) === 'N') {
            return [];
        }
        
        $ret = ['is_change_res' => true,];
        $default = ['new_controller_resource_prefix' => App::Current()->options['controller_resource_prefix'] ?? ''];
        $desc = "New resource url [{new_controller_resource_prefix}]\n";
        $input = Console::_()->readLines($default, $desc);
        $ret['new_controller_resource_prefix'] = $input['new_controller_resource_prefix'];
        
        
        $desc = "Clone Resource File from library to URL file? (Y/N)[{is_clone_resource}]?";
        $sure = Console::_()->readLines(['is_clone_resource' => 'Y'], $desc);
        
        $ret['is_clone_resource'] = (strtoupper($sure['is_clone_resource']) === 'Y') ? true: false;
        
        return $ret;
        
        App::Current()->options['controller_resource_prefix'] = $input['controller_resource_prefix'];
        
        if (strtoupper($sure['is_clone_resource']) === 'Y') {
            $info = '';
            
            RouteHookResource::_()->options['controller_resource_prefix'] = App::Current()->options['controller_resource_prefix'];
            RouteHookResource::_()->cloneResource(false, $info);
        }
    }
    public function doInstall()
    {
        $force = $this->args['force'] ?? false;
        //////////////////////////
        $install_level = App::Root()->options['installing_data']['install_level'] ?? 0;
        //echo ($install_level <= 0) ? "use --help for more info.\n" : '';
        $url_prefix = App::Current()->options['controller_url_prefix'];
        echo str_repeat("\t", $install_level)."\e[32;7mInstalling (".get_class(App::Current()).") to :\033[0m [$url_prefix]\n";
        
        if (!$force && App::Current()->isInstalled()) {
            echo "App as been installed. use --force to force \n";
            return;
        }
        if (!($this->args['skip_sql'] ?? false)) {
            DatabaseInstaller::_()->install($force);
        }
        RedisInstaller::_()->install($force);
        
        //////
        $validators = $this->options['install_input_validators'] ?? [];
        $default_options = $this->options['install_options'] ?? [];
        
        $resource_options = $this->changeResource();
        $default_options = array_merge($default_options, $resource_options);
        $desc = $this->options['install_input_desc'] ?? '--';
        $input_options = Console::_()->readLines($default_options, $desc, $validators);
        $input_options = array_merge($resource_options, $input_options);
       
        if ($this->args['dry'] ?? false) {
            echo "----\nInstall options dump:\n";
            return;
        }
        $flag = $this->doInstallAction($input_options);
        
        if ($this->is_failed) {
            echo "\e[32;3mInstalled App (".get_class(App::Current()).") FAILED!;\033[0m\n";
            return;
        }
        EventManager::FireEvent([App::Phase(), 'onInstall'], $input_options);
        
        ////////////////
        $ext_options = []; // 这里要其他更多的选项
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
    protected function doInstallAction($input_options)
    {
        if (!($this->args['skip_sql'] ?? false)) {
            SqlDumper::_()->install($this->args['force'] ?? false);
        }
        if ($input_options['is_change_res'] ?? false) {
            App::Current()->options['controller_resource_prefix'] = $input_options['new_controller_resource_prefix'];
            $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
            $ext_options['controller_resource_prefix'] = App::Current()->options['controller_resource_prefix'];
            ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
            
            if ($input_options['is_clone_resource']) {
                $info = '';
                RouteHookResource::_()->cloneResource(false, $info);
                if ($this->args['verbose'] ?? false) {
                    echo $info;
                }
            }
        }
        ($this->options['install_callback'])($input_options);
    }
    protected function saveInstalledFlag()
    {
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, App::Current());
        $ext_options['installed'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, App::Current());
    }
    //////////////////
}
