# DuckPhp\Component\GlobalAdmin
[toc]
## 简介

全局管理员，函

## 选项

无

## 方法

    public static function CallInPhase($phase)
基本方法
    public function action()

    public function service()

    public function id()
    public function data()


    public function checkLogin()

    public function current()

    public function isSuper()

    public function canAccessCurrent()
    public function canAccessUrl($url)
    public function canAccessCall($class, $method)
权限判定

    public function urlForRegist($url_back = null, $ext = null)
    public function urlForLogin($url_back = null, $ext = null)
    public function urlForLogout($url_back = null, $ext = null)
    public function urlForHome($url_back = null, $ext = null)
注册登录登出主页页面

    public function regist($post)
    public function login($post)
    public function logout($post)
注册登录登出动作

    public function getUsernames($ids)
获取一系列用户名

    protected function initOptions(array $options)
待定，初始化

## 说明

## 完毕




