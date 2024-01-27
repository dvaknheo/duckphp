<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Component\DuckPhpInstaller;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;
use DuckPhp\HttpServer\HttpServer;

class DuckPhpCommand extends ComponentBase
{
    /**
     * create new project in current diretory. --help for help
     */
    public function command_new()
    {
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
        echo $this->context()->version();
        echo "\n";
    }
    /**
     * show aviable commands.
     */
    public function command_list()
    {
        echo Console::_()->getCommandListInfo();
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
        echo "calling $class::_()->$method\n";
        $ret = Console::_()->callObject($class, $method, $args, Console::_()->getCliParameters());
        echo "--result--\n";
        echo json_encode($ret);
    }
    /**
     * fetch a url. --uri=??? ,
     */
    public function command_fetch($uri = '', $post = false)
    {
        $uri = !empty($uri) ? $uri : '/';
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['PATH_INFO'] = parse_url($uri, PHP_URL_PATH);
        $_SERVER['HTTP_METHOD'] = $post ? $post :'GET';
        $this->context()->options['cli_enable'] = false;
        $this->context()->run();
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
