<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace {
    //autoload file
    $autoload_file = __DIR__.'/../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    } else {
        $autoload_file = __DIR__.'/../../vendor/autoload.php';
        if (is_file($autoload_file)) {
            require_once $autoload_file;
        }
    }

}
////////////////////////////////////////

// This part is written by the core engineer.
namespace MySpace\System
{    
    use DuckPhp\DuckPhp;
    use DuckPhp\Ext\CallableView;
    use MySpace\View\Views;

    class App extends DuckPhp
    {
        // @override
        public $options = [
            'is_debug' => true,
                // turn on debug mode
            'path_info_compact_enable' => true,
                // single-file mode: it runs even without server configuration
            'ext' => [
                CallableView::class => true,
                // the default View cannot call functions, so we turn on the built-in
                // CallableView extension to replace the system View
            ],
            'callable_view_class' => Views::class,
                // the replacement View class.
        ];
        // @override
        protected function onInited(): void
        {
            //runs after initialisation.
            //var_dump($this->options);//show how many options there are in total
        }
    }

} // end namespace
// helper classes

//------------------------------
// The part below is written by the application engineer; it depends only loosely on
// DuckPhp's own classes. If you are a purist, it can be trimmed further.

namespace MySpace\Controller
{
    use DuckPhp\Foundation\Controller\ControllerHelper as Helper;
    use DuckPhp\Foundation\SingletonTrait;
    use MySpace\Business\MyBusiness;

    class MainController
    {
        use SingletonTrait;
        public function __construct()
        {
            // set the header/footer in the constructor.
            Helper::setViewHeaderFooter('header', 'footer');
        }
        public function index()
        {
            //fetch the data
            $output = "Hello, now time is " . __h(MyBusiness::_()->getTimeDesc()); // html encode
            $url_about = __url('about/me'); // url encode
            Helper::Show(get_defined_vars(), 'main_view'); //show the data
        }
    }
    class aboutController
    {
        public function me()
        {
            $url_main = __url(''); //default URL
            Helper::setViewHeaderFooter('header', 'footer');
            Helper::Show(get_defined_vars()); // default view about/me, may be omitted
        }
    }
} // end namespace

namespace MySpace\Business
{
    use MySpace\Model\MyModel;
    use DuckPhp\Foundation\Business\BusinessHelper as Helper;
    use DuckPhp\Foundation\SingletonTrait; //so that Business::_() is a replaceable singleton.

    class MyBusiness
    {
        use SingletonTrait;
        
        public function getTimeDesc()
        {
            return "<" . MyModel::getTimeDesc() . ">";
        }
    }

} // end namespace

namespace MySpace\Model
{
    //use DuckPhp\Foundation\Model\ModelHelper as Helper;
    use DuckPhp\Foundation\Model\ModelTrait;
    
    class MyModel
    {
        use ModelTrait;
        
        public static function getTimeDesc()
        {
            return date(DATE_ATOM);
        }
    }
}
// strip the PHP code away and this is the previewable HTML structure

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
// The entry point goes last, to avoid autoloading problems

namespace
{
    $options = [
        // 'override_class' => 'MySpace\System\App',
        // you may also adjust options here; they override the class options
    ];
    \MySpace\System\App::RunQuickly($options);
}
