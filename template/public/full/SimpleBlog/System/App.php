<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\DuckPhp;
use DuckPhp\Component\DbManager;
use DuckPhp\Ext\RouteHookRewrite;
use SimpleAuth\Api\SimpleAuthPlugin;
use SimpleBlog\Business\AdminBusiness;

class App extends DuckPhp
{
    //@override
    public $options = [
        'setting_file_enable' => true, // 启用设置文件
        'setting_file_ignore_exists' => false, // 忽略设置文件
        
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',
        
        'ext' => [
            RouteHookRewrite::class => true,    // 我们需要 重写 url
            SimpleAuthPlugin::class => [
                'simple_auth_check_installed' => true,  //       // 使用第三方的验证登录包
                'simple_auth_table_prefix' => 'sa_',
                'simple_auth_session_prefix' => '',
            ], 
        ],
        
        //url 重写
        'rewrite_map' => [
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ],
        
        //
        'simple_blog_check_installed' => true,  //       // 使用第三方的验证登录包
        'simple_blog_table_prefix' => '',
        'simple_auth_session_prefix' => '',
    ];
    protected function onPrepare()
    {
        // 我们要引入第三方包,这里我们没采用 composer。
        if (!class_exists(SimpleAuthApp::class)) {
            $path = realpath($this->options['path'].'../SimpleAuth/');
            $this->assignPathNamespace($path, 'SimpleAuth');
        }
    }
    protected function onBeforeRun()
    {
        $this->checkInstall();
    }
    protected function checkInstall()
    {
        if (!$this->options['simple_blog_check_installed']){
            return;
        }
        if (!(static::Setting('database') ||  static::Setting('database_list'))){
            throw new NeedInstallException('Need Database',NeedInstallException::NEED_DATABASE);
        }
        if (!Installer::G()->init([],$this)->isInstalled()){
            throw new NeedInstallException("",NeedInstallException::NEED_INSTALL);
        }
    }
    ////////////////////////////
    
    /** reset SimpleBlog password */
    public function command_reset_password()
    {
        $new_pass = AdminBusiness::G()->reset();
        echo 'new password: '.$new_pass;
        echo PHP_EOL;
    }
    /** Install SimpleBlog */
    public function command_install()
    {
        echo "Welcome to Use SimpleBlog installer  --force  to force install\n";
        $parameters =  static::Parameter();
        if(count($parameters)==1 || ($parameters['help'] ?? null)){
            // echo "--force  to force install ;";
            //return;
        }

        $this->install($parameters);
        $new_pass = AdminBusiness::G()->reset('123456');
        echo 'new password: '.$new_pass;
        echo PHP_EOL;
        
        echo "Done \n";
    }
    public function install($parameters)
    {
        $str=SimpleAuthPlugin::G()->install($parameters); // 检查子系统安装
        if($str){
            echo $str;
            return;
        }
        $options = [
            'force' => $parameters['force']?? false,
            'path' => $this->getPath(),
            //'sql_dump_prefix' => $this->options['simple_blog_table_prefix'],
            'sql_dump_inlucde_tables' =>['ActionLogs','Articles','Comments','Settings'], // 这里我们也要来个从 Model 里读取。
            'sql_dump_install_replace_prefix' => true,
            'sql_dump_install_new_prefix' => $this->options['simple_blog_table_prefix'],
            'sql_dump_install_drop_old_table' => $parameters['force']?? false,
        ];
        
        Installer::G()->init($options,$this);
        echo Installer::G()->run();
    }
    ///////////////////////
    public function getPath()
    {
        return $this->options['path'];
    }
    public function getTablePrefix()
    {
        return $this->options['simple_blog_table_prefix'];
    }
    public function getSessionPrefix()
    {
        return $this->options['simple_blog_session_prefix'];
    }
}
