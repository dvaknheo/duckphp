<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Ext\Installer;
use DuckPhp\HttpServer\HttpServer;

class Console extends ComponentBase
{
    use Console_Command;
    
    public $options = [
        'cli_enable' => true,
        'cli_mode' => 'replace',
        'cli_command_alias' => [],
    ];
    protected $context_class = null;
    protected $parameters = [];
    //@override
    protected function initContext(object $context)
    {
        if (PHP_SAPI !== 'cli') {
            return $this; // @codeCoverageIgnore
        }
        if (!$this->options['cli_enable']) {
            return;
        }
        if (method_exists($context, 'replaceDefaultRunHandler')) {
            $context->replaceDefaultRunHandler([static::class,'DoRun']);
        }
        $this->context_class = get_class($context);
        $this->options['cli_command_alias'][$this->context_class] = '';
    }
    public function getCliParameters()
    {
        return $this->parameters;
    }
    public function regCliCommandGroup($class, $alias)
    {
        $this->options['cli_command_alias'][$class] = $alias;
    }
    public static function DoRun()
    {
        return static::G()->run();
    }
    public function run()
    {
        $this->parameters = $this->parseCliArgs($_SERVER['argv']);
        $func_args = $this->parameters['--'];
        $cmd = array_shift($func_args);
        list($class, $method) = $this->getClassAndMethod($cmd);
        $this->callObject($class, $method, $func_args, $this->parameters);
        return true;
    }

    protected function parseCliArgs($argv)
    {
        $cli = array_shift($argv);
        $ret = [];
        $lastkey = '--';
        foreach ($argv as $v) {
            if (substr($v, 0, 2) === '--') {
                if (!isset($ret[$lastkey])) {
                    $ret[$lastkey] = true;
                }
                $lastkey = substr($v, 2);
            } elseif (!isset($ret[$lastkey])) {
                $ret[$lastkey] = $v;
            } elseif (is_array($ret[$lastkey])) {
                $ret[$lastkey][] = $v;
            } else {
                $t = $ret[$lastkey];
                $t = is_array($ret[$lastkey]) ? $t: [$t];
                $t[] = $v;
                $ret[$lastkey] = $t;
            }
        }
        if (!isset($ret[$lastkey])) {
            $ret[$lastkey] = true;
        }
        
        $args = $ret['--'];
        if (!is_array($args)) {
            $args = ($args === true)?'':$args;
            $ret['--'] = [$args?$args:'help'];
        }
        return $ret;
    }
    protected function callObject($class, $method, $args, $input)
    {
        $object = $class::G();
        $reflect = new \ReflectionMethod($object, $method);
        $params = $reflect->getParameters();
        foreach ($params as $i => $param) {
            $name = $param->getName();
            if (isset($input[$name])) {
                $args[$i] = $input[$name];
            } elseif ($param->isDefaultValueAvailable() && !isset($args[$i])) {
                $args[$i] = $param->getDefaultValue();
            } elseif (!isset($args[$i])) {
                throw new \ReflectionException("Command Need Parameter: {$name}\n", -2);
            }
        }
        
        $ret = $reflect->invokeArgs($object, $args);
        return $ret;
    }
    protected function getClassAndMethod($cmd)
    {
        $name = '';
        $method = $cmd;
        
        if (strpos($cmd, ':') !== false) {
            list($name, $method) = explode(':', $cmd);
        }
        $method = 'command_'.$method;
        
        $t = $this->options['cli_command_alias'];
        $alias = array_flip($t);
        $class = $alias[$name] ?? null;
        
        if (isset($class) && $name === '' && !method_exists($class, $method)) {
            $class = static::class;
            if (!method_exists($class, $method)) {
                throw new \ReflectionException("Command Not Found: {$cmd}\n", -2);
            }
            return [$class,$method];
        }
        
        $name = str_replace('/', '\\', $name);
        $class = $this->options['cli_command_alias'][$name] ?? $class;
        
        if (!isset($class)) {
            throw new \ReflectionException("Command Not Found: {$cmd}\n", -3);
        }
        return [$class,$method];
    }
    
    protected function getCommandsByClass($class)
    {
        $class = new \ReflectionClass($class);
        $methods = $class->getMethods();
        $ret = [];
        foreach ($methods as $v) {
            $name = $v->getName();
            if (substr($name, 0, strlen('command_')) !== 'command_') {
                continue;
            }
            $command = substr($name, strlen('command_'));
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
    protected function getCommandGroupInfo()
    {
        $ret = [
            'commands' => [],
            'alias' => [],
        ];
        $t = $this->options['cli_command_alias'];
        
        $ret['alias'] = array_flip(array_flip($t));
        
        foreach ($t as $class => $alias) {
            $data = $this->getCommandsByClass($class);
            if ($alias === '') {
                $data = array_map(function ($v) {
                    return "\e[32;1m*\e[0m".$v;
                }, $data);
            }
            $ret['commands'][$class] = $data;
        }
        $default = $this->getCommandsByClass(static::class);
        $app_class = $this->context_class;

        $ret['commands'][$app_class] = array_merge($default, $ret['commands'][$app_class]);
        
        foreach ($ret['commands'] as &$v) {
            ksort($v);
        }
        return $ret;
    }
}

trait Console_Command
{
    /**
     * create new project in current diretory.
     */
    public function command_new()
    {
        Installer::G()->init($this->getCliParameters())->run();
    }
    /**
     * run inner server.
     */
    public function command_run()
    {
        $options=$this->getCliParameters();
        $options['path']= $this->context_class::G()->options['path'];
        HttpServer::RunQuickly($options);
    }
    ///////////////////////////////////////
    /**
     * show this help.
     */
    public function command_help()
    {
        echo "Welcome to Use DuckPhp ,version: ";
        $this->command_version();
        echo  <<<EOT
Usage:
  command [arguments] [options] 
Options:
  --help            Display this help message
EOT;
        
        $this->command_list();
    }
    /**
     * show version
     */
    public function command_version()
    {
        echo  $this->context_class::G()->version();
        echo "\n";
    }
    /**
     * show aviable commands.
     */
    public function command_list()
    {
        $info = $this->getCommandGroupInfo();
        $str = '';
        foreach ($info['commands'] as $class => $v) {
            $class_alias = $info['alias'][$class] ?? null;
            if ($class_alias === '') {
                $str .= "system default commands\n";
            } elseif ($class_alias) {
                $str .= "commands power by '$class' alias '$class_alias':\n";
            } else {
                $str .= "commands power by '$class':\n";
            }
            foreach ($v as $cmd => $desc) {
                $cmd = "\e[32;1m".str_pad($cmd, 7)."\033[0m";
                $str .= "  $cmd $desc\n";
            }
            if ($class_alias === '') {
                $str .= "  \e[32;1m*\e[0m is overrided by '$class'.\n";
            }
        }
        echo $str;
    }
    /**
     * call a function. e.g. namespace/class@method arg1 --parameter arg2
     */
    public function command_call()
    {
        $args = func_get_args();
        $cmd = array_shift($args);
        list($class, $method) = explode('@', $cmd);
        $class = str_replace('/', '\\', $class);
        echo "calling $class::G()->$method\n";
        $ret = $this->callObject($class, $method, $args, $this->getCliParameters());
        echo "--result--\n";
        echo json_encode($ret);
    }
    /**
     * fetch a url
     */
    public function command_fetch($uri = '', $post = false)
    {
        $uri = !empty($uri) ? $uri : '/';
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['PATH_INFO'] = parse_url($uri, PHP_URL_PATH);
        $_SERVER['HTTP_METHOD'] = $post ? $post :'GET';
        $this->context_class::G()->replaceDefaultRunHandler(null);
        $this->context_class::G()->run();
    }
    ///////////////////////////////////
    /**
     * show all routes
     */
    public function command_routes()
    {
        echo "Override this to use to show you project routes .\n";
    }
    /**
     * depoly project.
     */
    public function command_depoly()
    {
        echo "Override this to use to depoly you project.\n";
    }
    /**
     * run test in you project
     */
    public function command_test()
    {
        echo "Override this to use to test you project.\n";
    }
}
