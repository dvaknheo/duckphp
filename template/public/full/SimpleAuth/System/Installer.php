<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\App;
use DuckPhp\Core\Configer;
use DuckPhp\Ext\SqlDumper;
use DuckPhp\SingletonEx\SingletonExTrait;

class Installer
{
    use SingletonExTrait;
    
    protected $key_installed_flag ='simple_auth_installed';
    
    public function isInstalled()
    {
        if(App::Setting($key_installed_flag)){
            return true;
        }
        return false;
    }
    protected function checkDb($database)
    {
        $options = DbManager::G()->options;
        $options['database'] = $database;
        DbManager::G()->init($options,App::G());
        DbManager::G()->_Db()->fetch('select 1+1 as t');
    }
    protected function getComponenetPath($path, $basepath = ''): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($path, 0, 1) === '/') {
                return rtrim($path, '/').'/';
            } else {
                return $basepath.rtrim($path, '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($path, 1, 1) === ':') {
                return rtrim($path, '\\').'\\';
            } else {
                return $basepath.rtrim($path, '\\').'\\';
            } // @codeCoverageIgnoreEnd
        }
    }
    protected function writeSettingFile($ext_setting)
    {
        $path = $this->getComponenetPath(Configer::G()->options['path_config'],Configer::G()->options['path']);
        $setting_file = $this->options['setting_file'] ?? 'setting';
        $file = $path.$setting_file.'.php';

        $setting = file_exists($file) ?  App::LoadConfig($setting_file) : [];
        $setting = array_merge($setting,$ext_setting);
        
        $data = '<'.'?php ';
        $data .="\n // gen by ".static::class.' '.date(DATE_ATOM) ." \n";
        $data .= ' return ';
        $data .= var_export($setting,true);
        $data .=';';
        
        return @file_put_contents($file,$data);
    }
    public function install($database)
    {
        $sqldumper_options = [
            'path'=>App::G()->options['path'],
        ];
        SqlDumper::G()->init($sqldumper_options, App::G());
        $database = [
            'dsn' => "mysql:host={$database['host']};port={$database['port']};dbname={$database['dbname']};charset=utf8mb4;",
            'username' => $database['username'],
            'password' => $database['password'],
            'driver_options' => [],
        ];        
        try{
            $this->checkDb($database);
            SqlDumper::G()->install();
        }catch(\Exception $ex){
            BaseException::ThrowOn(true, "安装数据库失败" . $ex->getMessage(),-1);
        }
        
        $ext_setting = [];
        $ext_setting['database'] = $database;
        
        $ext_setting[$key_installed_flag] = DATE(DATE_ATOM);
        
        $flag = $this->writeSettingFile($ext_setting);
        BaseException::ThrowOn(!$flag,'写入文件失败',-2);
        
        return true;
    }
    public function dumpSql()
    {
        $sqldumper_options = [
            'path'=>App::G()->options['path'],
            'sql_dump_inlucde_tables' =>['Users'],
        ];
        SqlDumper::G()->init($sqldumper_options,App::G());
        SqlDumper::G()->run();
    }
}