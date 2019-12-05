<?php
namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;
use MY\Base\Helper\ModelHelper as M;
use MY\Base\Helper\ViewHelper as V;
use MY\Base\Helper\ServiceHelper as S;



class Main
{
    public function __construct()
    {
        $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
        $is_no_path_info_mode=($path==='/no-path-info.php')?true:false;
        $url_phpinfo=C::URL('i');
        C::assignViewData(get_defined_vars());
    }
	public function index()
	{
        $ret=[];
        $x=C::GetExtendStaticStaticMethodList();
        $ret=array_keys($x);
        $z=new \ReflectionClass(C::class);
        $methods=$z->getMethods();
        foreach($methods as $v){
            $ret[]=$v->getName();
        }
        $str=implode("\n",$ret);
        echo $str;
            
		C::Show(get_defined_vars(),'main');
	}
    public function i()
    {
        phpinfo();
    }
}
