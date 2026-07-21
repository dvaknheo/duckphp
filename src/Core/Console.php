<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class Console extends ComponentBase
{
    public $options = [
        'console_command_classes' => [],
        'console_command_phase' => [],
        
        'console_command_default' => 'help',
        
        'console_readlines_logfile' => '',
    ];
    /*
    console_command_classes=> ['namespace'=>[class => method]];
    console_command_phase=> ['namespace'=> phase];
    //*/
    protected $context_class = null;
    protected $parameters = [];
    protected $is_inited = false;
    
    public $index = 0;
    public $data = '';

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
        return !empty($this->parameters)?$this->parameters:$this->parseCliArgs($_SERVER['argv']);
    }
    public function getArgs()
    {
        return $this->parameters['--'] ?? [];
    }
    public function app()
    {
        return $this->context();
    }
    public function regCommmandPrefixPhase($prefix, $phase)
    {
        $this->options['console_command_phase'][$prefix] = $phase;
    }
    public function regCommandClasses($prefix, array $classes)
    {
        $my_classes = $this->options['console_command_classes'][$prefix] ?? [];
        $my_classes = array_replace_recursive($my_classes, $classes);
        $this->options['console_command_classes'][$prefix] = $my_classes;
    }
    public function regCommandClassSingle(string $prefix, string $class, $method_prefix)
    {
        $this->options['console_command_classes'][$prefix][$class] = $method_prefix;
    }
    
    ////]]]]
    public static function DoRun($path_info = '')
    {
        return static::_()->run();
    }
    protected function splitCommand($cmd)
    {
        $command_namespace = '';
        $method = $cmd;
        $a = explode(':', $cmd);
        $cmd_method = array_pop($a);
        $command_namespace = implode(':', $a);
        return [$command_namespace,$cmd_method];
    }
    public function run()
    {
        $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $this->parameters = $this->parseCliArgs($my_server['argv']);
        $func_args = $this->parameters['--'];
        $cmd = array_shift($func_args);
        $cmd = $cmd ?? '';
        
        [$command_namespace,$cmd_method] = $this->splitCommand($cmd);

        [$class, $method] = $this->getCommandCallback($cmd);
        if (!isset($class) && !isset($method)) {
            throw new DuckPhpSystemException(" ($cmd)Command Not Found In All\n", -4);
        }
        $phase = $this->options['console_command_phase'][$command_namespace] ?? App::_()->getThisPhaseName();

        $old_phase = App::Phase($phase);
        $this->callObject($class, $method, $func_args, $this->parameters);
        App::Phase($old_phase);
        
        return true;
    }
    public function getCommandCallback($cmd)
    {
        [$command_namespace,$cmd_method] = $this->splitCommand($cmd);
        
        $classes = $this->options['console_command_classes'][$command_namespace] ?? [];
        
        if (empty($classes)) {
            return [null,null];
        }
        $cmd_method = str_replace('-', '_', $cmd_method);
        $classes = array_reverse($classes, true);
        foreach ($classes as $class => $method_prefix) {
            if (!isset($method_prefix) || $method_prefix === false) {
                continue;
            }
            $method_prefix = ($method_prefix === true) ? 'command_' : $method_prefix;
            
            $method = $method_prefix.$cmd_method;
            if (method_exists($class, $method)) {
                return [$class,$method];
            }
        }
        
        return [null,null];
    }
    public function readLinesFill($data)
    {
        $this->data .= $data;
    }
    public function readLinesCleanFill()
    {
        $this->data = '';
        $this->index = 0;
    }
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        $ret = [];
        $mode_fill = !$fp_in && !empty($this->data);
        if ($mode_fill) {
            $fp_in = fopen('php://memory', 'r+');
            if (!$fp_in) {
                return; // @codeCoverageIgnore
            }
            $str = $this->data;
            fputs($fp_in, $str);
            fseek($fp_in, $this->index);
        }
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
            
            $input = (string)fgets($fp_in);
            if ($this->options['console_readlines_logfile']) {
                $path = static::SlashDir(App::Root()->options['path']);
                $path_runtime = static::SlashDir(App::Root()->options['path_runtime']);
                $file = $this->options['console_readlines_logfile'];
                $file = static::IsAbsPath($file)?$file:$path_runtime.$file;
                
                file_put_contents($file, $input, FILE_APPEND);
            }
            if ($mode_fill) {
                echo $input;
                $this->index += strlen($input);
            }
            $input = trim($input);
            if ($input === '') {
                $input = $options[$key] ?? '';
            }

            $ret[$key] = $input;
        }
        if ($mode_fill) {
            fclose($fp_in);
        }
        $ret = !empty($validators)? filter_var_array($ret, $validators) :$ret;
        return $ret;
    }
    protected function parseCliArgs(array $argv): array
    {
        $cli = array_shift($argv);
        $ret = [];
        $lastkey = '--';
        foreach ($argv as $v) {
            if (substr($v, 0, 2) === '--') {
                if (!isset($ret[$lastkey])) {
                    $ret[$lastkey] = true;
                }
                //$lastkey = str_replace('-', '_', substr($v, 2)); // camel case?
                $lastkey = substr($v, 2);
                $pos = strpos($lastkey, '=');
                if ($pos !== false) {
                    $a = substr($lastkey, 0, $pos);
                    $b = substr($lastkey, $pos + 1);
                    $lastkey = $a;
                    $lastkey = str_replace('-', '_', $lastkey);
                    $ret[$lastkey] = $b;
                }
                $lastkey = str_replace('-', '_', $lastkey);
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
            $ret['--'] = [$args?$args:$this->options['console_command_default']];
        }
        return $ret;
    }
    protected function getObject(string $class): object
    {
        return is_callable([$class,'_']) ? $class::_() : new $class;
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
                throw new DuckPhpSystemException("Command Need Parameter: {$name}\n", -2);
            }
        }
        $ret = $reflect->invokeArgs($object, $args);
        return $ret;
    }
}
