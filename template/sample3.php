<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne as DuckPhpAllInOne;
use DuckPhp\Core\CoreHelper;

function __h($str)
{
    return CoreHelper::H($str);
}
class MyCoreHelper extends CoreHelper
{
    //@override
    public function _H(&$str)
    {
        return '<b>'.CoreHelper::_H($str).'</b>';
    }
}
class ExtApp extends DuckPhpAllInOne
{
    //@override
    public function onInited()
    {
        CoreHelper::_(MyCoreHelper::_());
        ExtApp::setViewHeadFoot('', '');
    }
    public function action_index()
    {
        ExtApp::Show([],'main');
    }
    public function view_main($data)
    {
        echo __h('<h!>');
        debug_print_backtrace(2);
    }
}
$options = [
    'path' => __DIR__ ,
];
ExtApp::RunQuickly($options);