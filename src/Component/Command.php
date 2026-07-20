<?php

declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;
use DuckPhp\HttpServer\HttpServer;

class Command extends ComponentBase
{
    /**
     * show version
     */
    public function command_version(): void
    {
        echo $this->context()->version();
        echo "\n";
    }
    /**
     * show this help.
     */
    public function command_help(): void
    {
        echo "Welcome to Use DuckPhp ,version: ";
        echo $this->context()->version();
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
        $this->context()->options['cli_enable'] = false;
        HttpServer::RunQuickly($options);
        $this->context()->options['cli_enable'] = true;
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
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            $sg = (__SUPERGLOBAL_CONTEXT)();
            $sg->_SERVER['REQUEST_URI'] = $uri;
            $sg->_SERVER['PATH_INFO'] = parse_url($uri, PHP_URL_PATH);
            $sg->_SERVER['HTTP_METHOD'] = $post ? $post : 'GET';
        } else {
            $_SERVER['REQUEST_URI'] = $uri;
            $_SERVER['PATH_INFO'] = parse_url($uri, PHP_URL_PATH);
            $_SERVER['HTTP_METHOD'] = $post ? $post : 'GET';
        }
        $this->context()->options['cli_enable'] = false;
        $this->context()->serve();
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
     * switch debug mode
     */
    public function command_debug(bool $off = false): void
    {
        $options = ExtOptionsLoader::_()->options;

        if ($this->context()->options['data_file_enable'] && $options['data_file_bump_allowed'] && in_array('is_debug', $options['data_file_bump_keys'])) {
            $is_debug = !$off;
            ExtOptionsLoader::_()->saveData(['is_debug' => $is_debug]);
            if ($is_debug) {
                echo "Debug mode has turn on. us --off to off\n";
            } else {
                echo "Debug mode has turn off.\n";
            }
        } else {
            echo "You need  turn on : data_file_enable, data_file_bump_allow and data_file_bump_keys ['is_debug'] \n";
        }
    }
    //////////////////
    protected function getCommandListInfo(): string
    {
        $str = '';
        $classes = Console::_()->options['console_command_classes'];

        foreach ($classes as $namespace => $v) {
            $phase = Console::_()->options['console_command_phase'][$namespace];

            $tip = ($namespace === '') ? '*Default commands*' : $namespace;
            $str .= "\e[32;7m{$tip}\033[0m \n"; //::{$v['class']}

            /////////////////
            $descs = $this->getCommandsByClasses($v, 'command_', $phase);

            ksort($descs);

            foreach ($descs as $method => $desc) {
                $cmd = !$namespace ? $method : $namespace . ':' . $method;
                $cmd = "\e[32;1m" . str_pad($cmd, 20) . "\033[0m";
                $str .= "  $cmd\t$desc\n";
            }
        }
        return $str;
    }
    protected function getCommandsByClasses(array $classes, string $method_prefix, string $phase): array
    {
        $ret = [];
        foreach ($classes as $class => $v) {
            if ($v === false) {
                continue;
            }
            $method_prefix = ($v === true) ? $method_prefix : $v;
            $desc = $this->getCommandsByClass($class, $method_prefix, $phase);
            $ret = array_merge($desc, $ret);
        }
        return $ret;
    }
    protected function getCommandsByClass(string $class, string $method_prefix, string $phase): array
    {
        // @phpstan-ignore-next-line
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
        return $ret;
    }
    protected function getCommandsByClassReflection(\ReflectionClass $ref, string $method_prefix): array
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
            $desc = ltrim('' . substr('' . $doc, 3));
            $pos = strpos($desc, "\n");
            $pos = ($pos !== false) ? $pos : 255;
            $desc = trim(substr($desc, 0, $pos), "* \t\n");
            $ret[$command] = $desc;
        }
        return $ret;
    }
}
