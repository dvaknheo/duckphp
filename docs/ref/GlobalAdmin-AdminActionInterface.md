    public function id();

    public function name();

    public function service();

    public function login(array $post);

    public function logout();

    public function urlForLogin($url_back = null, $ext = null);

    public function urlForLogout($url_back = null, $ext = null);

    public function urlForHome($url_back = null, $ext = null);

    public function checkAccess($class, string $method, ?string $url = null);

    public function isSuper();

    public function log(string $string, ?string $type = null);

    public function id($check_login = true);

    public function name($check_login = true);

