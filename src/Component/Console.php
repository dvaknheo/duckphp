<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

class Console
{
    public $options = [
        'cli_enable' => true,
        'cli_mode' => 'replace',
        'cli_command_alias' => [],
        'cli_default_command_class' => '',
        'cli_command_method_prefix' => 'command_',
        'cli_command_default' => 'help',
    ];
    protected $context_class = null;
    protected $parameters = [];
    protected $is_inited = false;
    
    protected static $_instances = [];
    //embed
    public static function G($object = null)
    {
        if (defined('__SINGLETONEX_REPALACER')) {
            return (__SINGLETONEX_REPALACER)(static::class, $object);
        }
        if ($object) {
            self::$_instances[static::class] = $object;
            return $object;
        }
        $me = self::$_instances[static::class] ?? null;
        if (null === $me) {
            $me = new static();
            self::$_instances[static::class] = $me;
        }
        
        return $me;
    }
    public function __construct()
    {
    }
    public function isInited(): bool
    {
        return $this->is_inited;
    }
    public function init(array $options, ?object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        if (PHP_SAPI !== 'cli') {
            return $this; // @codeCoverageIgnore
        }
        if (!$this->options['cli_enable']) {
            return;
        }
        if ($context !== null) {
            $this->context_class = get_class($context);
            if ($this->options['cli_mode'] === 'replace') {
                if (method_exists($context, 'replaceDefaultRunHandler')) {
                    $context->replaceDefaultRunHandler([static::class,'DoRun']);
                }
            } elseif ($this->options['cli_mode'] === 'hook') {
                ($this->context_class)::Route()->addRouteHook([static::class,'DoRun'], 'prepend-outter');
            }
        }
        $this->is_inited = true;
        return $this;
    }
    public function getCliParameters()
    {
        return $this->parameters;
    }
    public function regCommandClass($class, $alias = null)
    {
        $alias = $alias ?? $class;
        $this->options['cli_command_alias'][$class] = $alias;
    }
    public static function DoRun($path_info = '')
    {
        return static::G()->run();
    }
    public function run()
    {
        if ($this->options['cli_mode'] === 'replace' && method_exists($this->context_class, 'replaceDefaultRunHandler') && method_exists($this->context_class, 'G')) {
            $this->context_class::G()->replaceDefaultRunHandler(null);
        }
        $this->parameters = $this->parseCliArgs($_SERVER['argv']);
        $func_args = $this->parameters['--'];
        $cmd = array_shift($func_args);
        list($class, $method) = $this->getClassAndMethod($cmd);
        
        if ($this->context_class) {
            ($this->context_class)::Route()->setParameters($this->parameters);
        }
        $this->callObject($class, $method, $func_args, $this->parameters);
        return true;
    }
    public function app()
    {
        return $this->context_class::G();
    }
    ////[[[[
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        $ret = [];
        $fp_in = $fp_in ?? \STDIN;
        $fp_out = $fp_out ?? \STDOUT;
        
        $lines = explode("\n", trim($desc));
        foreach ($lines as $line) {
            $line = trim($line);
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
                $lastkey = str_replace('-', '_', substr($v, 2));
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
    public function callObject($class, $method, $args, $input)
    {
        $object = is_callable([$class,'G']) ? $class::G() : new $class;
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
        $method = $this->options['cli_command_method_prefix'].$method;
        if ($name === '') {
            $class = method_exists($this->context_class ?? '', $method) ? $this->context_class : $this->options['cli_default_command_class'];
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
            if ($class === $this->context_class) {
            }
        }
        $default = class_exists($this->options['cli_default_command_class']) ? $this->getCommandsByClass($this->options['cli_default_command_class']) :[];
        $default2 = $this->context_class ? $this->getCommandsByClass($this->context_class) : [];
        $default2 = array_map(function ($v) {
            return "\e[32;1m*\e[0m".$v;
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
        $info = $this->getCommandGroupInfo();
        $str = '';
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
            if ($class === '' && $this->context_class) {
                $str .= "  \e[32;1m*\e[0m is overrided by '{$this->context_class}'.\n";
            }
        }
        return $str;
    }
}
