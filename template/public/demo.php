<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace
{
    require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
}
// 以下部分是核心工程师写。
namespace MySpace\Base
{
    use DuckPhp\DuckPhp;
    use DuckPhp\Ext\CallableView;
    use DuckPhp\Ext\RouteHookOneFileMode; // 我们要支持无路由的配置模式
    use MySpace\View\Views;

    class App extends DuckPhp
    {
        // @override
        public $options = [
            'is_debug' => true,
                // 开启调试模式
            'skip_setting_file' => true,
                // 本例特殊，跳过设置文件 这个选项防止没有上传设置文件到服务器
            'use_path_info_by_get' => true,// 开启单一文件模式，服务器不配置也能运行
            'ext' => [
                RouteHookPathInfoByGet::class => true,
                CallableView::class => true,
                    // 默认的 View 不支持函数调用，我们用扩展 CallableView 代替系统的 View
            ],
            'callable_view_class' => Views::class, 
                    // 替换的 View 类。
        ];
        protected function onInit()
        {
            //初始化之后在这里运行。
            //var_dump($this->options);//查看总共多少选项
        }
        protected function onRun()
        {
            //运行期在这里
        }
    }
    //服务基类, 为了 XXService::G() 可变单例。
    class BaseService
    {
        use \DuckPhp\Core\SingletonEx;
    }
} // end namespace
// 助手类
namespace MySpace\Base\Helper
{
    class ControllerHelper extends \DuckPhp\Helper\ControllerHelper
    {
        // 添加你想要的助手函数
    }
    class ServiceHelper extends \DuckPhp\Helper\ServiceHelper
    {
        // 添加你想要的助手函数
    }
    class ModelHelper extends \DuckPhp\Helper\ModelHelper
    {
        // 添加你想要的助手函数
    }
    class ViewHelper extends \DuckPhp\Helper\ViewHelper
    {
        // 添加你想要的助手函数
    }
} // end namespace
//------------------------------
// 以下部分由应用工程师编写，不再和 DuckPhp 的类有任何关系。
namespace MySpace\Controller
{
    use MySpace\Base\Helper\ControllerHelper as C;  // 引用助手类
    use MySpace\Service\MyService;                  // 引用相关服务类

    class Main
    {
        public function __construct()
        {
            // 在构造函数设置页眉页脚。
            C::setViewHeadFoot('header', 'footer');
        }
        public function index()
        {
            //获取数据
            $output = "Hello, now time is " . C::H(MyService::G()->getTimeDesc());
            $url_about = C::URL('about/me');
            C::Show(get_defined_vars(), 'main_view'); //显示数据
        }
    }
    class about
    {
        public function me()
        {
            $url_main = C::URL(''); //默认URL
            C::setViewHeadFoot('header', 'footer');
            C::Show(get_defined_vars()); // 默认视图 about/me ，可省略
        }
    }
} // end namespace
namespace MySpace\Service
{
    use MySpace\Base\Helper\ServiceHelper as S;
    use MySpace\Base\BaseService;
    use MySpace\Model\MyModel;

    class MyService extends BaseService
    {
        public function getTimeDesc()
        {
            return "<" . MyModel::getTimeDesc() . ">";
        }
    }

} // end namespace
namespace MySpace\Model
{
    use MySpace\Base\Helper\ModelHelper as M;

    class MyModel
    {
        public static function getTimeDesc()
        {
            return date(DATE_ATOM);
        }
    }
}
// 把 PHP 代码去掉看，这是可预览的 HTML 结构
namespace MySpace\View {
    class Views
    {
        public static function header($data)
        {
            extract($data); ?>
            <html>
                <head>
                </head>
                <body>
                <header style="border:1px gray solid;">I am Header</header>
    <?php
        }

        public static function main_view($data)
        {
            extract($data); ?>
            <h1><?=$output?></h1>
            <a href="<?=$url_about?>">go to "about/me"</a>
    <?php
        }
        public static function about_me($data)
        {
            extract($data); ?>
            <h1> OK, go back.</h1>
            <a href="<?=$url_main?>">back</a>
    <?php
        }
        public static function footer($data)
        {
            ?>
            <footer style="border:1px gray solid;">I am footer</footer>
        </body>
    </html>
    <?php
        }
    }
} // end namespace
//------------------------------
// 入口，放最后面避免自动加载问题
namespace {
    $options = [
        'namespace' => 'MySpace', //项目命名空间为 MySpace，  你可以随意命名
    ];
    \DuckPhp\DuckPhp::RunQuickly($options);
}
