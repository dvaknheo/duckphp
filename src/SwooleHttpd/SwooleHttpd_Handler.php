<?php
namespace SwooleHttpd;

trait SwooleHttpd_Handler
{
    public static function OnShow404()
    {
        return static::G()->_OnShow404();
    }
    public static function OnException($ex)
    {
        return static::G()->_OnException($ex);
    }
    public function _OnShow404()
    {
        if ($this->http_404_handler) {
            ($this->http_404_handler)($ex);
            return;
        }
        static::header('', true, 404);
        echo "DNMVCS swoole mode: Server 404 \n";
    }
    public function _OnException($ex)
    {
        if ($this->http_exception_handler) {
            ($this->http_exception_handler)($ex);
            return;
        }
        static::header('', true, 500);
        echo "DNMVCS swoole mode: Server Error. \n";
        echo var_export($ex);
    }
}
