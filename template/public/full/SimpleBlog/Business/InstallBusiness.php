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
        if(App::Setting('database')){
            return true;
        }
        return false;
    }
    public function install($host,$port,$dbname,$username,$password)
    {
        $setting = App::LoadConfig('setting');
        $setting['database'] = [
            'dsn' => "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4;",
            'username' => $username,
            'password' => $password,
            'driver_options' => [],
        ];
        $setting_file = App::G()->options['path'].''
        var_dump($setting_file);
        //file_put_contents($setting_file,var_export($setting,true));
        
        return true;
    }
}