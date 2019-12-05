<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core\Helper;

use DuckPhp\Core\Helper\HelperTrait;
use DuckPhp\Core\App;

class ServiceHelper
{
    use HelperTrait;
    
    public static function Setting($key)
    {
        return App::Setting($key);
    }
    public static function Config($key, $file_basename = 'config')
    {
        return App::Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return App::LoadConfig($file_basename);
    }
}
