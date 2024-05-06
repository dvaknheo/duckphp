<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Component\DuckPhpInstaller;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;
use DuckPhp\HttpServer\HttpServer;

trait CommandTrait
{
    /**
     * show version
     */
    public function command_version()
    {
        echo App::Current()->version();
        echo "\n";
    }
    /**
     * show this help.
     */
    public function command_help()
    {
        echo "Welcome to Use DuckPhp ,version: ";
        echo App::Current()->version();
        echo "\n";
        echo  <<<EOT
Usage:
  command [arguments] [options] 
Options:
  --help            Display this help message

EOT;
        echo $this->getCommandListInfo();
    }
    /**
     * create new project in current diretory. --help for help
     */
    public function command_new($namespace = '')
    {
        //ifempty(readLines();
        DuckPhpInstaller::_()->init(Console::_()->getCliParameters())->run();
    }
    /**
     * run inner server.
     */
    public function command_run()
    {
        $options = Console::_()->getCliParameters();
        $options['http_app_class'] = get_class($this->context());
        $options['path'] = $this->context()->options['path'];
        if (!empty($options['http_server'])) {
            /** @var string */
            $class = str_replace('/', '\\', $options['http_server']);
            HttpServer::_($class::_());
        }
        HttpServer::RunQuickly($options);
    }
    /**
     * fetch a url. --uri=[???] ,--post=[postdata]
     */
    public function command_fetch($uri = '', $post = false)
    {
        $args = Console::_()->getCliParameters();
        $real_uri = $args['--'][1] ?? null;
        $uri = $url ?? $real_uri;
        
        $uri = !empty($uri) ? $uri : '/';
        // TODO no need uri ,  directrer
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['PATH_INFO'] = parse_url($uri, PHP_URL_PATH);
        $_SERVER['HTTP_METHOD'] = $post ? $post :'GET';
        App::Current()->options['cli_enable'] = false;
        App::Current()->run();
    }
    /**
     * call a function. e.g. namespace/class@method arg1 --parameter arg2
     */
    public function command_call()
    {
        //call to service
        // full namespace , service AAService;
        // TODO ，no fullnamespace
        $args = func_get_args();
        $cmd = array_shift($args);
        list($class, $method) = explode('@', $cmd);
        $class = str_replace('/', '\\', $class);
        echo "calling $class::_()->$method\n";
        $ret = Console::_()->callObject($class, $method, $args, Console::_()->getCliParameters());
        echo "--result--\n";
        echo json_encode($ret);
    }
    /**
     * show all routes
     */
    public function command_routes()
    {
        echo "Override this to use to show your project routes .\n";
    }
    /**
     * switch debug mode
     */
    public function command_debug($off = false)
    {
        $is_debug = !$off;
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
    //////////////////
    protected function getCommandListInfo()
    {
        $str = '';
        $group = Console::_()->options['cli_command_group'];
        
        foreach ($group as $namespace => $v) {
            $tip = ($namespace === '')? '*Default commands*':$namespace;
            $str .= "\e[32;7m{$tip}\033[0m {$v['phase']}\n";//::{$v['class']}
            
            /////////////////
            $descs = $this->getCommandsByClasses($v['classes'], $v['method_prefix'], $v['phase']);
            ksort($descs);
            foreach ($descs as $method => $desc) {
                $cmd = !$namespace ? $method : $namespace.':'.$method;
                $cmd = "\e[32;1m".str_pad($cmd, 20)."\033[0m";
                $str .= "  $cmd\t$desc\n";
            }
        }
        return $str;
    }
    protected function getCommandsByClasses($classes, $method_prefix, $phase)
    {
        $ret = [];
        foreach ($classes as $class) {
            if (is_array($class)) {
                list($class, $method_prefix) = $class;
            }
            $desc = $this->getCommandsByClass($class, $method_prefix, $phase);
            $ret = array_merge($desc, $ret);
        }
        return $ret;
    }
    protected function getCommandsByClass($class, $method_prefix, $phase)
    {
        $ref = new \ReflectionClass($class);
        if ($ref->hasMethod('getCommandsOfThis')) {
            return (new $class)->getCommandsOfThis($method_prefix, $phase);
        }
        return $this->getCommandsByClassReflection($ref, $method_prefix);
    }
    public function getCommandsOfThis($method_prefix, $phase)
    {
        $class = new \ReflectionClass($this);
        $ret = $this->getCommandsByClassReflection($class, $method_prefix);
        if ($phase != App::Phase()) {
            unset($ret['new']);
            unset($ret['run']);
            unset($ret['help']);
        }
        return $ret;
    }
    protected function getCommandsByClassReflection($ref, $method_prefix)
    {
        $methods = $ref->getMethods();
        $ret = [];
        foreach ($methods as $v) {
            $name = $v->getName();
            if (substr($name, 0, strlen($method_prefix)) !== $method_prefix) {
                continue;
            }
            $command = substr($name, strlen($method_prefix));
            $doc = $v->getDocComment();
            
            // first line;
            $desc = ltrim(''.substr(''.$doc, 3));
            $pos = strpos($desc, "\n");
            $pos = ($pos !== false)?$pos:255;
            $desc = trim(substr($desc, 0, $pos), "* \t\n");
            $ret[$command] = $desc;
        }
        return $ret;
    }
}
