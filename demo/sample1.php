<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne;
use DuckPhp\Foundation\Controller\Helper;

use MyApp as MyWelcomeController;
use MyApp as MyBusiness;
use MyApp as MyModel;
use MyApp as MyView;

class MyApp extends DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__ ,
        'controller_welcome_class' => MyWelcomeController::class,
        'callable_view_class' => MyView::class,
        // ...
    ];
    //@override
    public function onInited()
    {
        Helper::setViewHeadFoot('', '');
    }
    public function action_index()
    {
        $words = MyBusiness::_()->getTime();
        Helper::Show(['words'=>$words], 'main');
    }

    public function view_main($data)
    {
        $url = __url('');
        echo "You are visit: $url; {$data['words']}";
    }
    public function getTime()
    {
        return "Hello,now is <".MyModel::_()->getData().'>';
    }
    public function getData()
    {
        return DATE(DATE_ATOM);
    }
}
MyApp::RunQuickly([]);