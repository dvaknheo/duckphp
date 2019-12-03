<?php
namespace MY\Controller;

use MY\Base\App as DN;
use MY\Base\Helper\ControllerHelper as C;
use MY\Service\TestService;
use DNMVCS\Ext\JsonRpcExt;
use DNMVCS\Ext\RedisManager;

class Main
{
	public function index()
	{
    
    $x=C::Logger();
    $window_length=3;
    var_dump(floor($window_length/2));
        //return;
        //
        //$t=TestService::G(JsonRpcExt::Wrap(TestService::class))->foo();
        //var_dump($t);
//var_dump(DN::SG());var_dump(DATE(DATE_ATOM));exit;
		//DN::ThrowOn(true,"JustError",123);
		$data=array();
		$data['var']=TestService::foo();
        $data['html_pager']=C::Pager()::Render(123);
        
		C::Show($data,'main');
	}
    public function json_rpc()
    {
        $ret= JsonRpcExt::G()->onRpcCall(DN::SG()->_POST);
        C::ExitJson($ret);
    }
	public function i()
	{
        $options=[
            'redis_list'=>[[
                'host'=>'127.0.0.1',
                'port'=>'6379',
                'auth'=>'cgbauth',
                'select'=>'2',
            ]],
        ];
        RedisManager::G()->init($options,DN::G());
        var_dump(DN::GetExtendStaticStaticMethodList());
        /*
        $x=DN::Redis()->get('A');
        var_dump($x);
        DN::Redis()->set('A',DATE(DATE_ATOM));
        */
        $y=DN::SimpleCache()->get('B');
        var_dump($y);
        DN::SimpleCache()->set('B',DATE(DATE_ATOM));
        
        var_dump(DATE(DATE_ATOM));
        return;
        C::setMultiExceptionHandler([\Exception::class],function($ex){
            debug_print_backtrace(2);
        });
        C::ThrowOn(true,"ZZ");
    
		$data=array();
		C::Show($data);
	}
    public function hello($name = 'ThinkPHP6')
    {
echo <<<EOT
<fieldset>
<legend>调用堆栈</legend>
<pre>
EOT;

debug_print_backtrace(2);
echo <<<EOT
</pre>
</fieldset>
EOT;
        return 'hello,' . $name;
    }
}
