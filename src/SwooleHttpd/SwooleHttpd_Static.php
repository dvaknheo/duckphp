<?php
namespace SwooleHttpd;

trait SwooleHttpd_Static
{
    public static function Server()
    {
        return static::G()->server;
    }
    public static function Request()
    {
        return SwooleContext::G()->request;
    }
    public static function Response()
    {
        return SwooleContext::G()->response;
    }
    public static function Frame()
    {
        return SwooleContext::G()->frame;
    }
    public static function FD()
    {
        return SwooleContext::G()->fd;
    }
    public static function IsClosing()
    {
        return SwooleContext::G()->isWebSocketClosing();
    }
}
