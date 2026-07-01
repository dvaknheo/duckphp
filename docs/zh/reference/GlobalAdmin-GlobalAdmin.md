# DuckPhp\GlobalAdmin\GlobalAdmin
[toc]
## 简介
全局管理员类
对应 Helper::Admin() 
## 公开方法

    public function service()
获得Service对象

    public function id($check_login = true) : int
`Helper::AdminId()`的实现，获得当前管理员ID， ch

    public function name($check_login = true)
`Helper::AdminId()`的实现，获得当前管理员ID， ch

    public function login(array $post)
登录动作

    public function logout()
登出动作

    public function urlForLogin($url_back = null, $ext = null)
登入URL

    public function urlForLogout($url_back = null, $ext = null)
登出URL

    public function urlForHome($url_back = null, $ext = null)
管理员主页URL

    public function checkAccess($class, string $method, ?string $url = null)
检查权限

    public function isSuper()
判断是否是管理员

    public function log(string $string, ?string $type = null)
记录日志



