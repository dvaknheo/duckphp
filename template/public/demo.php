<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */

// 以下部分是核心工程师写。

namespace MySpace\System
{
    // 自动加载文件
    require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
    
    use DuckPhp\Core\SingletonTrait;
    use DuckPhp\DuckPhp;
    use DuckPhp\Ext\CallableView;
    use MySpace\View\Views;

    class App extends DuckPhp
    {
        // @override 重写
        public $options = [
            'is_debug' => true,
                // 开启调试模式
            'path_info_compact_enable' => true,
                // 开启单一文件模式，服务器不配置也能运行
            'ext' => [
                CallableView::class => true,
                // 默认的 View 不支持函数调用，我们开启自带扩展 CallableView 代替系统的 View
            ],
            'callable_view_class' => Views::class,
                // 替换的 View 类。
        ];
        // @override 重写
        protected function onInit()
        {
            //初始化之后在这里运行。
            //var_dump($this->options);//查看总共多少选项
        }
    }
    //服务基类, 为了 Business::_() 可变单例。
    class BaseBusiness
    {
        use SingletonTrait;
    }
} // end namespace
// 助手类

//------------------------------
// 以下部分由应用工程师编写，不再和 DuckPhp 的类有任何关系。

namespace MySpace\Controller
{
    use MySpace\Business\MyBusiness;  // 引用助手类
    class Helper
    {
        use \DuckPhp\Helper\ControllerHelperTrait;
        // 添加你想要的助手函数
    }
    class MainController
    {
        public function __construct()
        {
            // 在构造函数设置页眉页脚。
            Helper::setViewHeadFoot('header', 'footer');
        }
        public function action_index()
        {
            //获取数据
            $output = "Hello, now time is " . __h(MyBusiness::_()->getTimeDesc()); // html编码
            $url_about = __url('about/me'); // url 编码
            Helper::Show(get_defined_vars(), 'main_view'); //显示数据
        }
    }
    class aboutController
    {
        public function action_me()
        {
            $url_main = __url(''); //默认URL
            Helper::setViewHeadFoot('header', 'footer');
            Helper::Show(get_defined_vars()); // 默认视图 about/me ，可省略
        }
    }
} // end namespace

namespace MySpace\Business
{
    use MySpace\Helper\BusinessHelper as B;
    use MySpace\Model\MyModel;
    use MySpace\System\BaseBusiness;
    class BusinessHelper
    {
        use  \DuckPhp\Helper\BusinessHelperTrait;
        // 添加你想要的助手函数
    }
    class MyBusiness extends BaseBusiness
    {
        public function getTimeDesc()
        {
            return "<" . MyModel::getTimeDesc() . ">";
        }
    }

} // end namespace

namespace MySpace\Model
{
    use MySpace\Helper\ModelHelper as M;
    class ModelHelper
    {
        use \DuckPhp\Helper\ModelHelperTrait;
        // 添加你想要的助手函数
    }
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

namespace
{
    $options = [
        // 'override_class' => 'MySpace\System\App',
        // 你也可以在这里调整选项。覆盖类内选项
    ];
    \MySpace\System\App::RunQuickly($options);
}
