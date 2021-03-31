<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\HttpServer;

interface HttpServerInterface
{
    //public $options = [];
    public static function RunQuickly($options);
    public function run();
    public function getPid();
    public function close();
}
