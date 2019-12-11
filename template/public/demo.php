<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace {
    require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
    //头文件可以自行修改。
}
// 以下部分是核心程序员写。
namespace MySpace\Base
{
    use \DuckPhp\Core\View;
    use \DuckPhp\Ext\CallableView;

    // 默认的View 不支持函数调用，我们这里替换他。
    class App extends \DuckPhp\App
    {
        protected function onInit()
        {
            // 本例特殊，这里演示函数调用的   CallableView 代替系统的 View
            $this->options['callable_view_class'] = 'MySpace\View\Views';
            View::G(CallableView::G());
            
            ////
            return parent::onInit();
        }
    }
    //服务基类, 为了 XXService::G() 可变单例。
    class BaseService
    {
        use \DuckPhp\SingletonEx;
    }
    // 模型基类, 为了 XXModel::G() 可变单例。
    class BaseModel
    {
        use \DuckPhp\SingletonEx;
    }
} // end namespace
// 助手类
namespace MySpace\Base\Helper
{
    class ControllerHelper extends \DuckPhp\Helper\ControllerHelper
    {
        // 一般不需要添加东西，继承就够了
    }
    class ServiceHelper extends \DuckPhp\Helper\ServiceHelper
    {
        // 一般不需要添加东西，继承就够了
    }
    class ModelHelper extends \DuckPhp\Helper\ModelHelper
    {
        // 一般不需要添加东西，继承就够了
    }
    class ViewHelper extends \DuckPhp\Helper\ViewHelper
    {
        // 一般不需要添加东西，继承就够了
    }
} // end namespace
// 以下部分是普通程序员写的。不再和 DuckPhp 的类有任何关系。
namespace MySpace\Controller {

    use MySpace\Base\Helper\ControllerHelper as C;
    use MySpace\Service\MyService;

    class Main
    {
        public function __construct()
        {
            //设置页眉页脚。
            C::setViewWrapper('header', 'footer');
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
            $url_main = C::URL('');
            C::setViewWrapper('header', 'footer');
            C::Show(get_defined_vars());
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
            return "<" . MyModel::G()->getTimeDesc() . ">";
        }
    }

} // end namespace
namespace MySpace\Model
{
    use MySpace\Base\Helper\ModelHelper as M;
    use MySpace\Base\BaseModel;

    class MyModel extends BaseModel
    {
        public function getTimeDesc()
        {
            return date(DATE_ATOM);
        }
    }

}
// 把 PHP 代码去掉看，这是可预览的 HTML 结构
namespace MySpace\View {
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
// 以下部分是核心程序员写。
// 这里是入口，单一文件下要等前面类声明
namespace {
    $options = [];
    $options['namespace'] = rtrim('MySpace\\', '\\'); //项目命名空间为 MySpace，  你可以随意命名
    $options['is_debug'] = true;  // 开启调试模式
    
    $options['skip_app_autoload'] = true; // 本例特殊，跳过app 用的 autoload 免受干扰
    $options['skip_setting_file'] = true; // 本例特殊，跳过设置文件
    
    \DuckPhp\App::RunQuickly($options, function () {
    });
} // end namespace
