# DuckPhp\FastInstaller\FastInstaller

[toc]

## 简介
FastInstaller 是 DuckPhp 内置的一个安装系统。
用于在命令行下安装 DuckPhp 应用。


## 方法
    public function doCommandInstall()

    public function doInstall()


    protected function configDatabase($force = false)

    protected function configRedis($force = false)

    private function initComponents()

    protected function showHelp($app_options = [], $input_options = [])

    protected function doGlobalConfigure()

    protected function adjustPrompt($desc, $default_options, $ext_options, $app_options)

    protected function installChildren()

    protected function doInstallAction($input_options = [], $ext_options = [], $app_options = [])

    protected function reduce_apps($object, $callback)

    public function command_install()

    public function command_require()

    public function command_update()

    public function command_remove()

    protected function initComponents()

    public function doCommandUpdate()

    public function doCommandRemove()

    public function forceFail()

    public function getCurrentInput()

    protected function doInstallAction()

    protected function saveInstalledFlag()

    public function command_dumpsql()

        'install_input_validators' => [],

        'install_options' => [],

        'install_input_desc' => '',

        'install_callback' => null,

    protected function getDefaultUrlPrefix($ns)

    protected function changeResource()

    protected function doInstallAction($input_options)

    protected function saveExtOptions($ext_options)

        'install_default_options' => [],

    public function command_dump_res()

    protected function cloneResource($dest)

