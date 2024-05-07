<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class Console extends ComponentBase
{
    public $options = [
        'cli_command_group' => [ ],
        'cli_command_default' => 'help',
    ];
    /*
    cli_command_group=>
    [   namespace => [
                'phase'=>'duckphp\duckphp'
                'class'=>'Command::class',
                'method_prefix'=>'command_',
        ],
    ]
    //*/
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
    public function regCommandClass($command_namespace, $phase, $classes, $method_prefix = 'command_')
    {
        $this->options['cli_command_group'][$command_namespace] = [
            'phase' => $phase,
            'classes' => $classes,
            'method_prefix' => $method_prefix,
        ];
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
        
        $command_namespace = '';
        $method = $cmd;
        if (strpos($cmd, ':') !== false) {
            list($command_namespace, $method) = explode(':', $cmd);
        }
        $group = $this->options['cli_command_group'][$command_namespace] ?? null;
        if (empty($group)) {
            throw new \ReflectionException("Command Not Found: {$cmd}\n", -3);
        }
        
        //$method = $group['method_prefix'].$method;
        //$class = $group['class'];
        App::Phase($group['phase']);
        // get class ,and method, then call
        list($class, $method) = $this->getCallback($group, $method);
        if (!isset($class) && !isset($method)) {
            throw new \ReflectionException("Command Not Found In All\n", -4);
        }
        
        $this->callObject($class, $method, $func_args, $this->parameters);
        return true;
    }
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        $ret = [];
        $fp_in = $fp_in ?? fopen('php://stdin', 'r'); //\STDIN;//
        $fp_out = $fp_out ?? fopen('php://stdout', 'w'); //\STDOUT;//
        
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
                $lastkey = str_replace('-', '_', substr($v, 2)); // camel case?
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
    public function getCallback($group, $cmd_method)
    {
        //$method = $group['method_prefix'].$method;
        //$class = $group['class'];
        foreach ($group['classes'] as $class) {
            if (is_array($class)) {
                list($class, $method_prefix) = $class;
                $method = $method_prefix.$cmd_method;
            } else {
                $method = $group['method_prefix'].$cmd_method;
            }
            if (method_exists($class, $method)) {
                return [$class,$method];
            }
        }
        return [null,null];
    }
    public function callObject($class, $method, $args, $input)
    {
        //TODO $args =[];
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
        $ret = $reflect->invokeArgs($object, $args);
        return $ret;
    }
}
