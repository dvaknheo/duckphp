# DuckPhp\Component\CommandTrait
[toc]
## 说明
命令行合集 Trait。
##
    public function command_call()

    public function command_routes()

    public function command_debug($off = false)

    public function command_version()

    public function command_run()

    public function command_help()

    public function command_fetch($uri = '', $post = false)

    protected function getCommandListInfo()

    protected function getCommandsByClass($class, $method_prefix)

    public function command_new()

    public function command_new($namespace = '')

    protected function getCommandsByClasses($classes, $method_prefix, $phase)

    protected function getCommandsByClass($class, $method_prefix, $phase)

    public function getCommandsOfThis($method_prefix, $phase)

    protected function getCommandsByClassReflection($ref, $method_prefix)

