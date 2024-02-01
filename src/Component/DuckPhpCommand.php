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
        echo $this->context()->version();
        echo "\n";
        echo  <<<EOT
Usage:
  command [arguments] [options] 
Options:
  --help            Display this help message

EOT;
        
        echo Console::_()->getCommandListInfo();
    }
    /**
     * fetch a url. --uri=[???] ,--post=[postdata]
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
     * override to run test in you project
     */
    public function command_test()
    {
        echo "override to run test in you project.\n";
    }
}
