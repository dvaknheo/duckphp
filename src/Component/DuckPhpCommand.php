<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Component\Console;
use DuckPhp\Component\Installer;
use DuckPhp\Core\ComponentBase;
use DuckPhp\HttpServer\HttpServer;

class DuckPhpCommand extends ComponentBase
{
    protected $context_class = null;
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
    }
    /**
     * create new project in current diretory. --help for help
     */
    public function command_new()
    {
        Installer::G()->init(Console::G()->getCliParameters())->run();
    }
    /**
     * run inner server.
     */
    public function command_run()
    {
        $options = Console::G()->getCliParameters();
        $options['http_app_class'] = $this->context_class;
        $options['path'] = $this->context_class::G()->options['path'];
        if (!empty($options['http_server'])) {
            /** @var string */
            $class = str_replace('/', '\\', $options['http_server']);
            HttpServer::G($class::G());
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
        echo  $this->context_class::G()->version();
        echo "\n";
    }
    /**
     * show aviable commands.
     */
    public function command_list()
    {
        echo Console::G()->getCommandListInfo();
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
        $ret = Console::G()->callObject($class, $method, $args, Console::G()->getCliParameters());
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
