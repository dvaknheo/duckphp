<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Business;

use SimpleBlog\Helper\BusinessHelper as B;
use SimpleBlog\System\App;
use DuckPhp\Component\DbManager;

class InstallBusiness extends BaseBusiness
{
    //
    public function checkInstall()
    {
        if(App::Setting('simple_blog_installed')){
            return true;
        }
        return false;
    }
    //
    public function install($database)
    {
        $setting = App::LoadConfig('setting');
        $database = [
            'dsn' => "mysql:host={$database['host']};port={$database['port']};dbname={$database['dbname']};charset=utf8mb4;",
            'username' => $database['username'],
            'password' => $database['password'],
            'driver_options' => [],
        ];
        //接下来连接数据库
        try{
            $this->checkDb($database);
            $sqldumper_options = [
                //
            ];
            SqlDumper::G()->init($sqldumper_options,App::G())->install();
        }catch(\Exception $ex){
            BusinessException::ThrowOn(true, "安装数据库失败" . $ex->getMessage(),-1);
        }
        $setting['database'] = $database;
        $setting['simple_blog_installed'] = DATE(DATE_ATOM);
        
        $flag = $this->writeSettingFile($setting);
        BusinessException::ThrowOn(!$flag,'写入文件失败',-2);
        
        return true;
    }
    protected function writeSettingFile($setting)
    {
        $setting = App::LoadConfig(App::G()->options['setting_file']?? 'setting');
        
        $path = $this->getComponenetPathByKey('path_config'); // TODO;
        $setting_file = $this->options['setting_file'] ?? 'setting';
        $file = $path.$setting_file.'.php';
        
        $data = '<'.'?php ';
        $data .="\n // gen by ".static::class.' '.date(DATE_ATOM) ." \n";
        $data .= ' return ';
        $data .= var_export($setting,true);
        $data .=';';
        return @file_put_contents($file,$data);
    }
    protected function CheckDb($database)
    {
        $options = DbManager::G()->options;
        $options['database']=$database;
        DbManager::G()->init($options,App::G());
    }
}