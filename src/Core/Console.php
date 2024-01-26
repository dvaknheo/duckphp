<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class Console extends ComponentBase
{
    public $options = [
        'cli_command_alias' => [],
        'cli_default_command_class' => '',
        'cli_command_method_prefix' => 'command_',
        'cli_command_default' => 'help',
    ];
    protected $context_class = null;
    protected $parameters = [];
    protected $is_inited = false;
    

    public function init(array $options, ?object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        if ($context !== null) {
            $this->context_class = get_class($context);
        }
        $this->is_inited = true;
        return $this;
    }
    public function getCliParameters()
    {
        return $this->parameters;
    }
    public function app()
    {
        return $this->context();
    }
    
    public function regCommandClass($class, $alias = null)
    {
        $alias = $alias ?? $class;
        $this->options['cli_command_alias'][$class] = $alias;
    }
    public static function DoRun($path_info = '')
    {
        return static::_()->run();
    }
    public function run()
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $this->parameters = $this->parseCliArgs($_SERVER['argv']);
        $func_args = $this->parameters['--'];
        $cmd = array_shift($func_args);
        list($class, $method) = $this->getClassAndMethod($cmd);
        
        $this->callObject($class, $method, $func_args, $this->parameters);
        return true;
    }
    ////[[[[
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        $ret = [];
        $fp_in = $fp_in ?? \STDIN;
        $fp_out = $fp_out ?? \STDOUT;
        
        $lines = explode("\n", trim($desc));
        foreach ($lines as $line) {
            $line = rtrim($line).' ';
            $flag = preg_match('/\{(.*?)\}/', $line, $m);
            if (!$flag) {
                fputs($fp_out, $line."\n");
                continue;
            }
            $key = $m[1];
            $line = str_replace('{'.$key.'}', $options[$key] ?? '', $line);
            fputs($fp_out, $line);
            $input = trim((string)fgets($fp_in));
            if ($input === '') {
                $input = $options[$key] ?? '';
            }
            $ret[$key] = $input;
        }
        $ret = !empty($validators)? filter_var_array($ret, $validators) :$ret;
        return $ret;
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
                $lastkey = str_replace('-', '_', substr($v, 2)); //这里还要有个驼峰扩展
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
            $ret['--'] = [$args?$args:$this->options['cli_command_default']];
        }
        return $ret;
    }
    protected function getObject($class)
    {
        return is_callable([$class,'_']) ? $class::_() : new $class;
    }
    public function callObject($class, $method, $args, $input)
    {
        $object = $this->getObject($class);
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
        if ($class !== $this->options['cli_default_command_class']) {
            App::Phase($class); //-- ugly
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
        $method = $this->options['cli_command_method_prefix'].$method;
        if ($name === '') {
            $context_class = get_class($this->context()); //this to fix
            $class = method_exists($context_class ?? '', $method) ? $context_class : $this->options['cli_default_command_class'];
        } else {
            $name = str_replace('/', '\\', $name);
            
            $t = $this->options['cli_command_alias'];
            $alias = array_flip($t);
            $class = $alias[$name] ?? null;
            $class = $class ?? $name;
        }
        // $this->options['cli_default_command_class'] ===''
        if (!$class || !class_exists($class)) {
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
            if (substr($name, 0, strlen($this->options['cli_command_method_prefix'])) !== $this->options['cli_command_method_prefix']) {
                continue;
            }
            $command = substr($name, strlen($this->options['cli_command_method_prefix']));
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
            'commands' => [
                '' => [],
            ],
            'alias' => [],
        ];
        
        $t = $this->options['cli_command_alias'];
        $ret['alias'] = array_flip(array_flip($t));
        
        foreach ($t as $class => $alias) {
            $data = $this->getCommandsByClass($class);
            $ret['commands'][$class] = $data;
        }
        
        $context_class = get_class(App::_());
        $default = class_exists($this->options['cli_default_command_class']) ? $this->getCommandsByClass($this->options['cli_default_command_class']) :[];
        $default2 = $context_class ? $this->getCommandsByClass($context_class) : [];
        $default2 = array_map(function ($v) {
            return "\e[32;1m* \e[0m".$v;
        }, $default2);
        $default = array_merge($default, $default2);
        
        $ret['commands'][''] = $default;
        
        foreach ($ret['commands'] as &$v) {
            ksort($v);
        }
        return $ret;
    }
    public function getCommandListInfo()
    {
        //TODO: Move out
        $info = $this->getCommandGroupInfo();
        $str = '';
        $context_class = get_class(App::_());
        foreach ($info['commands'] as $class => $v) {
            $class_alias = $info['alias'][$class] ?? null;
            if ($class === '') {
                $alias = '';
                $str .= "System default commands:\n";
            } elseif ($class_alias === $class || empty($class_alias)) {
                $alias = $class .':';
                $str .= "Commands power by '$class':\n";
            } else {
                $alias = $class_alias.':';
                $str .= "Commands power by '$class' alias '$class_alias':\n";
            }
            
            foreach ($v as $cmd => $desc) {
                $cmd = "\e[32;1m".$alias. str_pad($cmd, 7)."\033[0m";
                $str .= "  $cmd $desc\n";
            }
            if ($class === '' && $context_class) {
                $str .= "  \e[32;1m*\e[0m is overrided by '{$context_class}'.\n";
            }
        }
        return $str;
    }
}
