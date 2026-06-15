<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Lang;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\SuperGlobal;;

class LangApp extends DuckPhp
{

}
class MyLang extends Lang
{
}
class LangTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Lang::class);
		$path=\LibCoverage\LibCoverage::G()->getClassTestPath(Lang::class);

		DuckPhp::_()->init([
			'path'=>$path,
            'is_debug'=>true,
			'lang_default'=>'en_US',
			'app'=> [
				LangApp::class => [
					'path'=>$path,
					'is_debug'=>true,
				],
			],
        ]);
        __l("Hello {YOU}", ['YOU'=>'me']);
		Lang::_()->options['lang_final']=null;
		__l("Hello {YOU}", ['YOU'=>'me']);
		
		Lang::_()->options['lang_final']='NoExists';
		__l("Hello {YOU}", ['YOU'=>'me']);
		Lang::_()->options['lang_final']='en_US';
		__l("BBB");
		
		////////////////
		
		MyLang::_(new MyLang())->init([
			'lang_detect_mode'=>['NoExists'],
		]);
		
		SuperGlobal::DefineSuperGlobalContext();
		MyLang::_(new MyLang())->init([
			//'lang_detect_mode'=>['NoExists'],
		]);
		SuperGlobal::_()->_SERVER['HTTP_ACCEPT_LANGUAGE']='zh-CN,zh;q=0.9,en;q=0.8';
		MyLang::_(new MyLang())->init([
			//'lang_detect_mode'=>['NoExists'],
		]);
		SuperGlobal::_()->_SERVER['HTTP_ACCEPT_LANGUAGE']='';
		MyLang::_(new MyLang())->init([
			//'lang_detect_mode'=>['NoExists'],
		]);
		
		putenv('LANG=zh_CN.UTF-8');
		MyLang::_(new MyLang())->init([
			//'lang_detect_mode'=>['NoExists'],
		]);
	
		DuckPhp::_()->init([
			'path'=>$path,
            'is_debug'=>true,
			'lang_default'=>'zh_CN',
			'lang_simple_mode_only_sentences'=>[
				'zh_CN'=>[
					'AAA'=>'zh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CN',
				],
			],
        ]);
		
		
		echo __l("AAA");
        \LibCoverage\LibCoverage::End();
    }
}