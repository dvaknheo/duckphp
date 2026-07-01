    public function id($check_login = true) : int;

    public function name($check_login = true) : string;

    public function service();

    public function login(array $post);

    public function logout();

    public function regist(array $post);

    public function urlForLogin($url_back = null, $ext = null) : string;

    public function urlForLogout($url_back = null, $ext = null) : string;

    public function urlForHome($url_back = null, $ext = null) : string;

    public function urlForRegist($url_back = null, $ext = null) : string;


