# DuckPhp\FastInstaller\Supporter
[toc]
## 简介
给安装器支持类。 
##选项
        'database_driver_supporter_map' => [

          'mysql' => SupporterByMysql::class,

          'sqlite' => SupporterBySqlite::class,

          ],
## 方法
    public static function Current()

    public function getSupporter()

    public function getInstallDesc()

    public function readDsnSetting($options)

    public function writeDsnSetting($options)

    public function getAllTable()

    public function getSchemeByTable($table)

## 说明