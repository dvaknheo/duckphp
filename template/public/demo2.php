<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace {
    require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
    //头文件可以自行修改。
}

// 以下部分是核心代码。
namespace MySpace\Base
{
    // 默认的View 不支持函数调用，我们这里替换他。
    class App extends \DuckPhp\App
    {
        protected function onInit()
        {
            // 本例特殊，这里演示函数调用的 扩展 CallableView 代替系统的 View
            $this->options['ext']['DuckPhp\Ext\CallableView'] = [
                'callable_view_class' => 'MySpace\View\Views',
            ];
            ////
            return parent::onInit();
        }
    }
} // end namespace
// 以下部分是业务代码
namespace MySpace\Controller //控制器
{
    use MySpace\Base\App;
    use MySpace\Service\MyService;

    class Main
    {
        public function __construct()
        {
            App::setViewWrapper('header', 'footer');//设置页眉页脚。
        }
        public function index() //主页
        {
            //获取数据
            $output = "Hello, now time is " . App::H(MyService::getTimeDesc());
            $url_about = App::URL('about/me');
            App::Show(get_defined_vars(), 'main_view'); //显示数据
        }
    }
    class about
    {
        public function me()  //about/me
        {
            $url_main = App::URL('');
            App::setViewWrapper('header', 'footer');
            App::Show(get_defined_vars());// 默认的view 名称没了。
        }
    }
} // end namespace
namespace MySpace\Service
{
    use MySpace\Base\App;
    use MySpace\Model\MyModel;

    class MyService
    {
        public static function getTimeDesc()
        {
            return "<" . MyModel::getTimeDesc() . ">";
        }
    }

} // end namespace
namespace MySpace\Model
{
    use MySpace\Base\App;

    class MyModel
    {
        public static function getTimeDesc()
        {
            return date(DATE_ATOM);
        }
    }

}
// 把 PHP 代码去掉看，这是可预览的 HTML 结构
namespace MySpace\View
{
    class Views
    {
        public function header($data)
        {
            extract($data); ?>
            <html>
                <head>
                </head>
                <body>
                <header style="border:1px gray solid;">I am Header</header>
    <?php
        }

        public function main_view($data)
        {
            extract($data); ?>
            <h1><?=$output?></h1>
            <a href="<?=$url_about?>">go to "about/me"</a>
    <?php
        }
        public function about_me($data)
        {
            extract($data); ?>
            <h1> OK, go back.</h1>
            <a href="<?=$url_main?>">back</a>
    <?php
        }
        public function footer($data)
        {
            ?>
            <footer style="border:1px gray solid;">I am footer</footer>
        </body>
    </html>
    <?php
        }
    }
} // end namespace
// 以下部分是核心代码
// 入口文件，默认在 public/index.php
namespace
{
    $options = [];
    $options['namespace'] = rtrim('MySpace\\', '\\'); //项目命名空间为 MySpace，  你可以随意命名
    $options['is_debug'] = true;  // 开启调试模式
    
    $options['skip_app_autoload'] = true; // 本例特殊，跳过app 用的 autoload 免受干扰
    $options['skip_setting_file'] = true; // 本例特殊，跳过设置文件
    
    //没设置服务器，那就用 _r 作为路由吧
    $options['ext']['DuckPhp\Ext\RouteHookOneFileMode'] = [
       'key_for_action' => '_r',
       'key_for_module' => '',
    ];
    
    \DuckPhp\App::RunQuickly($options);
} // end namespace
