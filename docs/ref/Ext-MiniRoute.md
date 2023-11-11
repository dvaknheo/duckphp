#
对 route 的精简版本
        'namespace' => '',

        'namespace_controller' => 'Controller',

        'controller_path_ext' => '',

        'controller_welcome_class' => 'Main',

        'controller_welcome_class_visible' => false,

        'controller_welcome_method' => 'index',

        'controller_class_postfix' => '',

        'controller_method_prefix' => '',

        'controller_class_map' => [],

        'controller_resource_prefix' => '',

        'controller_url_prefix' => '',

    public static function Route()

    public function run()

    protected function pathToClassAndMethod($path_info)

    public function defaultGetRouteCallback($path_info)

    public function getControllerNamespacePrefix()

    public function replaceController($old_class, $new_class)

    public static function PathInfo($path_info = null)

    public function _PathInfo($path_info = null)

    protected function getPathInfo()

    protected function setPathInfo($path_info)

    public function getRouteError()

    public function getRouteCallingPath()

    public function getRouteCallingClass()

    public function getRouteCallingMethod()

    public function setRouteCallingMethod($calling_method)

    public static function Url($url = null)

    public static function Res($url = null)

    public static function Domain($use_scheme = false)

    public function _Url($url = null)

    public function _Res($url = null)

    public function _Domain($use_scheme = false)

    protected function getUrlBasePath()

