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

