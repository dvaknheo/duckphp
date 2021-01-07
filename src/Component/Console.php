<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Component\DefaultCommand;
use DuckPhp\Core\ComponentBase;

class Console extends ComponentBase
{
    public $options = [
        'cli_enable' => true,
        'cli_mode' => 'replace',
        'cli_command_alias' => [],
        'cli_default_command_class' => DefaultCommand::class,
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
        
        $this->context_class = get_class($context);
        
        if ($this->options['cli_mode'] === 'replace') {
            if (method_exists($context, 'replaceDefaultRunHandler')) {
                $context->replaceDefaultRunHandler([static::class,'DoRun']);
            }
        } elseif ($this->options['cli_mode'] === 'hook') {
            ($this->context_class)::Route()->addRouteHook([static::class,'DoRun'], 'prepend-outter');
        }
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
    public static function DoRun($path_info = '')
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
    public function app()
    {
        return $this->context_class::G();
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
                $pos = strpos($lastkey, '=');
                if ($pos !== false) {
                    $a = substr($lastkey, 0, $pos);
                    $b = substr($lastkey, $pos + 1);
                    $lastkey = $a;
                    $ret[$lastkey] = $b;
                }
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
    public function callObject($class, $method, $args, $input)
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
            $class = $this->options['cli_default_command_class'];
            if (!method_exists($class, $method)) {
                throw new \ReflectionException("Command Not Found: {$cmd}\n", -2);
            }
            $options = $this->context_class::G()->options;
            $options = $options['ext'][$class] ?? $options;
            $options = is_string($options) ? $this->context_class::G()->options[$options] : $options;
            $options = is_array($options) ? $options : [];
            $class::G()->init($options, $this);
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
    public function getCommandGroupInfo()
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
        $default = $this->getCommandsByClass($this->options['cli_default_command_class']);
        $app_class = $this->context_class;

        $ret['commands'][$app_class] = array_merge($default, $ret['commands'][$app_class]);
        
        foreach ($ret['commands'] as &$v) {
            ksort($v);
        }
        return $ret;
    }
}
