<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

use DuckPhp\Core\App;

class ConsoleCommand extends ComponentBase
{
    public function help()
    {
        var_dump("wellcome to use DuckPhp Console");
    }
    public function version()
    {
        //var_dump(\DuckPhp\DuckPhp::VERSION);
    }
    public function run($route)
    {
        
    }
    public function list()
    {
        echo "Show you the list\n";
    }
    
    public function server($host,$port)
    {
        \DuckPhp\HttpServer\HttpServer::RunQuickly();
    }
    
    public function install()
    {
        //Installer::G()->init([],App::G)->run();
    }
    public function publish()
    {
        //
    }
    public function routes()
    {
        //
    }
}
