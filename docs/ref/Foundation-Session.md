# DuckPhp\Foundation\Session
[toc]

## 简介

Session 提供了一个 Session 数据管理的基类，你可以在这上面扩充。
## 选项

        'session_prefix' => '',
session 的前缀
## 方法

    protected function checkSessionStart()
检查 session 是否启动

    protected function get($key, $default = null)
获取seesion

    protected function set($key, $value)
设置 seesion

    protected function unset($key)
取消 session

## 说明
