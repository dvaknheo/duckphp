<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Business;

use SimpleBlog\Helper\BusinessHelper as B;
use SimpleBlog\System\App;

class InstallBusiness extends BaseBusiness
{
    public function checkInstall()
    {
        if(B::Setting('simple_blog_installed')){
            return true;
        }
        return false;
    }
    public function install($database)
    {
        //extract($database);
        $setting = B::LoadConfig('setting');
        $database = [
            'dsn' => "mysql:host={$database['host']};port={$database['port']};dbname={$database['dbname']};charset=utf8mb4;",
            'username' => $database['username'],
            'password' => $database['password'],
            'driver_options' => [],
        ];
        //接下来连接数据库
        try{
            App::G()->checkDb($database);
            App::Db()->execute($this->getSqlForStruct());
            App::Db()->execute($this->getSqlForData());
        }catch(\Exception $ex){
            BusinessException::ThrowOn(true, "安装数据库失败" . $ex->getMessage(),-1);
        }
        $setting['database'] = $database;
        $setting['simple_blog_installed'] = DATE(DATE_ATOM);
        
        $flag = App::G()->writeSettingFile($setting);
        BusinessException::ThrowOn(!$flag,'写入文件失败',-2);
        
        return true;
    }
    
    //
    protected function getSqlForStruct()
    {
        return include App::G()->options['path'].'data/'.'database_struct.php';
    }
    protected function getSqlForData()
    {
        return include App::G()->options['path'].'data/'.'database_data.php';
    }
}