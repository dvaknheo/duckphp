<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Controller;

use ProjectNameTemplate\Business\DemoBusiness;
use ProjectNameTemplate\Controller\Base;
use ProjectNameTemplate\Controller\Helper;

class MainController extends Base
{
    public function action_index()
    {
        //change it if  you can
        $var = __h(DemoBusiness::_()->foo());
        Helper::Show(get_defined_vars(), 'main');
    }
    public function action_files()
    {
        Helper::Show(get_defined_vars(), 'files');
    }
    public function action_i()
    {
        phpinfo();
    }
    protected function action_foo()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function action_doc()
    {
        $file = Helper::GET('f');
        $view_file = dirname($_SERVER['SCRIPT_FILENAME']).'/doc.php';
        define('IN_VIEW',true);
        if(!$file){
            Helper::Show([], $view_file);
            return;
        }
        $str = DemoBusiness::_()->getDocData($file);
        if(!$str){
            Helper::Show([],$view_file);
            return;
        }
        
        if (substr($file, -4) === '.svg') {
            Helper::header('content-type:image/svg+xml');
            echo $str;
        } elseif (substr($file, -3) === '.md') {
            Helper::header('content-type:application/json');
            echo json_encode(['s' => $str], JSON_UNESCAPED_UNICODE); // 纯文本太折腾，用json
        }
        Helper::exit();
    }
}
