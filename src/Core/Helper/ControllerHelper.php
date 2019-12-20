<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core\Helper;

use DuckPhp\Core\Helper\HelperTrait;
use DuckPhp\Core\App;

class ControllerHelper
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
    ////
    public static function H($str)
    {
        return App::H($str);
    }
    public static function L($str, $args = [])
    {
        return App::L($str, $args);
    }
    public static function HL($str, $args = [])
    {
        return App::HL($str, $args);
    }
    public static function ShowBlock($view, $data = null)
    {
        return App::ShowBlock($view, $data);
    }
    public static function URL($url)
    {
        return App::URL($url);
    }
    public static function Domain()
    {
        return App::Domain();
    }
    ////
    public static function Parameters()
    {
        return App::Parameters();
    }
    public static function getRouteCallingMethod()
    {
        return App::getRouteCallingMethod();
    }
    public static function setRouteCallingMethod($method)
    {
        return App::setRouteCallingMethod($method);
    }
    public static function getPathInfo()
    {
        return App::getPathInfo();
    }
    ///////////////
    public static function Show($data = [], $view = null)
    {
        return App::Show($data, $view);
    }
    public static function setViewWrapper($head_file = null, $foot_file = null)
    {
        return App::setViewWrapper($head_file, $foot_file);
    }
    public static function assignViewData($key, $value = null)
    {
        return App::assignViewData($key, $value);
    }
    ////////////////////
    public static function ExitRedirect($url, $exit = true)
    {
        return App::ExitRedirect($url, $exit);
    }
    public static function ExitRedirectOutside($url, $exit = true)
    {
        return App::ExitRedirectOutside($url, $exit);
    }
    public static function ExitRouteTo($url, $exit = true)
    {
        return App::ExitRedirect(static::URL($url), $exit);
    }
    public static function Exit404($exit = true)
    {
        return App::Exit404($exit);
    }
    public static function ExitJson($ret, $exit = true)
    {
        return App::ExitJson($ret, $exit);
    }
    /////////////////
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return App::header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return App::setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return App::exit($code);
    }
    //exception manager
    public static function assignExceptionHandler($classes, $callback = null)
    {
        return App::assignExceptionHandler($classes, $callback);
    }
    public static function setMultiExceptionHandler(array $classes, $callback)
    {
        return App::setMultiExceptionHandler($classes, $callback);
    }
    public static function setDefaultExceptionHandler($callback)
    {
        return App::setDefaultExceptionHandler($callback);
    }
    //super global
    public static function SG()
    {
        return App::SG();
    }
}
