<?php

declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Component;

use DuckPhp\Component\RouteLister;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;
use DuckPhp\HttpServer\HttpServer;

class Command extends ComponentBase
{
    /**
     * @command_desc show version
     */
    public function command_version(): void
    {
        echo $this->context()->version();
        echo "\n";
    }
    /**
     * @command_desc show this help.
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
     * @command_desc run inner server.
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
     * @command_desc fetch a url. --uri=[???] ,--post=[postdata]
     */
    public function command_fetch($uri = '', $post = false)
    {
        $args = Console::_()->getCliParameters();
        $real_uri = $args['--'][1] ?? null;
        $uri = $uri ?? $real_uri;

        $uri = !empty($uri) ? $uri : '/';
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
        //$this->context()->options['cli_enable'] = false;
        $this->context()->serve();
    }
    /**
     * @command_desc call a function. e.g. namespace/class@method arg1 --parameter arg2
     */
    public function command_call()
    {
        $args = func_get_args();
        $cmd = array_shift($args);
        list($class, $method) = explode('@', $cmd);
        $class = str_replace('/', '\\', $class);
        if ('\\'!==substr($class,0,1)) {
            $namespace = '' . $this->context()->options['namespace'];
            $class = $namespace . 'Business\\' . $class;
        } else {
            $class = ltrim($class, '\\');
        }

        echo "calling $class::_()->$method\n";
        $ret = Console::_()->callObject($class, $method, $args, Console::_()->getCliParameters());
        echo "--result--\n";
        echo json_encode($ret);
    }
    /**
     * @command_desc {{command.routes|show routes}}
     */
    public function command_routes(bool $with_children = true, bool $only_controller = false, bool $only_admin = false, bool $only_user = false): void
    {
        $routes = RouteLister::_()->listAll($with_children, $only_controller, $only_admin, $only_user);
        foreach ($routes as $route) {
            // url line: green background
            echo "\033[42;30m" . $route['url'] . "\033[0m\n";
            $extra = '';
            if (!empty($route['controller'])) {
                $extra = $route['controller'] . '->' . $route['method'];
            }
            $marks = [];
            if ($route['route_map']) {
                $marks[] = 'route_map';
            }
            if ($route['route_map_important']) {
                $marks[] = 'route_map_important';
            }
            if ($route['rewrite_map']) {
                $marks[] = 'rewrite_map';
            }
            if ($marks) {
                $extra = $extra ? $extra . ' ' : '';
                $extra .= '(' . implode(',', $marks) . ')';
            }
            // admin/user: red marks at the end, without brackets
            $admin_user = '';
            if ($route['is_admin']) {
                $admin_user .= ' admin';
            }
            if ($route['is_user']) {
                $admin_user .= ' user';
            }
            $admin_user = $admin_user !== '' ? "\033[31m" . $admin_user . "\033[0m" : '';
            $phase = $route['phase'];
            $phase_str = $phase !== '' ? ' (' . $phase . ')' : '';
            echo '  ' . $extra . $phase_str . $admin_user . "\n";
        }
    }
    /**
     * @command_desc switch debug mode
     */
    public function command_debug(bool $off = false): void
    {
        $options = ExtOptionsLoader::_()->options;

        if ($this->context()->options['data_file_enable'] && $options['data_file_bump_allowed'] && in_array('is_debug', $options['data_file_bump_keys'])) {
            $is_debug = !$off;
            ExtOptionsLoader::_()->saveExtOptions(['is_debug' => $is_debug]);
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
    /**
     * @param array<string, mixed> $classes
     * @param array<string, mixed> $classes
     * @param array<string, mixed> $classes
     */
    /**
     * @param array<string, mixed> $classes
     * @return array<string, mixed>
     */
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
    /**
     * @return array<string, mixed>
     */
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
    /**
     * @return array<string, mixed>
     */
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

            $desc = '';
            if ($doc !== false) {
                if (preg_match('/@command_desc\s+([^\n]+)/', $doc, $m)) {
                    $desc = trim($m[1]);
                } else {
                    // fallback: first line of doc comment
                    $desc = ltrim(substr($doc, 3));
                    $pos = strpos($desc, "\n");
                    $pos = ($pos !== false) ? $pos : 255;
                    $desc = trim(substr($desc, 0, $pos), "* \t\n");
                }
            }
            $ret[$command] = $this->translateCommandDesc($desc);
        }
        return $ret;
    }
    /**
     * Translate command description: replace {{lang_key|default_fallback}} / {{lang_key}} placeholders.
     * @param string $desc
     * @return string
     */
    protected function translateCommandDesc(string $desc): string
    {
        return App::_()->langText($desc);
    }
}
