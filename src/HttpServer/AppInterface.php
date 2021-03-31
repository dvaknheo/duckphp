<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\HttpServer;

interface AppInterface
{
    public static function G();
    public static function assignExceptionHandler();
    public static function system_wrapper_replace();
    public static function On404();
    public function run();
    public function skip404Handler();
    public function getDynamicComponentClasses();
}
