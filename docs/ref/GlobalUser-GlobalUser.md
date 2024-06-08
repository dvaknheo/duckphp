# DuckPhp\GlobalAdmin\GlobalAdmin
[toc]
## 简介
全局管理员类
对应 Helper::User() 

    public function service()
获得服务对象

    public function id($check_login = true) : int
对应
    public function name($check_login = true) : string

    public function login(array $post)

    public function logout()

    public function regist(array $post)

    public function urlForLogin($url_back = null, $ext = null) : string

    public function urlForLogout($url_back = null, $ext = null) : string

    public function urlForHome($url_back = null, $ext = null) : string

    public function urlForRegist($url_back = null, $ext = null) : string

    public function batchGetUsernames($ids)

