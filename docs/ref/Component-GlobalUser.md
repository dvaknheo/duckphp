# DuckPhp\Component\GlobalUser
[toc]
## 简介

全局用户

## 选项

无

## 方法

    public function __construct()

    public function id()

    public function data()

    public function logoutUrl($ext)

    public function nick()

    public function username()

    public static function CallInPhase($phase)

    public function checkLogin()

    public function current()

    public function urlForRegist($url_back = null, $ext = null)

    public function urlForLogin($url_back = null, $ext = null)

    public function urlForLogout($url_back = null, $ext = null)

    public function urlForHome($url_back = null, $ext = null)

    public function regist($post)

    public function login($post)

    public function logout($post)

