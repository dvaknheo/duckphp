<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp;

use DuckPhp\Core\HttpServer as Server;

class HttpServer extends Server
{
    protected $cli_options_ex = [
            'swoole' => [
                'desc' => 'Use swoole httpserver',
            ],
    ];
    public function __construct()
    {
        $this->cli_options = array_replace_recursive($this->cli_options_ex, $this->cli_options);
        parent::__construct();
    }
    protected function checkSwoole()
    {
        if (!function_exists('swoole_version')) {
            return false; // @codeCoverageIgnore
        }
        return true; // @codeCoverageIgnore
    }
    protected function runHttpServer()
    {
        if ($this->cli_options['swoole'] && $this->checkSwoole()) {
            return $this->runSwooleServer($this->options['path'], $this->host, $this->port); // @codeCoverageIgnore
        }
        return parent::runHttpServer();
    }
    protected function runSwooleServer($path, $host, $port)
    {
        $ext = ($host === '0.0.0.0')?" ( http://127.0.0.1:$port/ )":'';
        
        echo "DuckPhp: RunServer by SwooleHttpd http://$host:$port/$ext\n";
        
        $dn_options = $this->options['dnmvcs'] ?? [];
        $dn_options['path'] = $path;
        $dn_options['swoole'] = $dn_options['swoole'] ?? [];
        $dn_options['swoole']['host'] = $host;
        $dn_options['swoole']['port'] = $port;

        if (defined('DuckPhp_WARNING_IN_TEMPLATE')) {
            $dn_options['skip_setting_file'] = true;
            echo "Don't run the template file directly \n";
        }
        App::RunQuickly($dn_options);
    }
}
