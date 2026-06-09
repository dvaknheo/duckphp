<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Locale;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\SuperGlobal;;

class LocaleApp extends DuckPhp
{

}
class MyLocale extends Locale
{
}
class LocaleTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Locale::class);
		$path=\LibCoverage\LibCoverage::G()->getClassTestPath(Locale::class);

		DuckPhp::_()->init([
			'path'=>$path,
            'is_debug'=>true,
			'locale_lang_default'=>'en_US',
			'app'=> [
				LocaleApp::class => [
					'path'=>$path,
					'is_debug'=>true,
				],
			],
        ]);
        __l("Hello {YOU}", ['YOU'=>'me']);
		Locale::_()->options['locale_lang_final']=null;
		__l("Hello {YOU}", ['YOU'=>'me']);
		
		Locale::_()->options['locale_lang_final']='NoExists';
		__l("Hello {YOU}", ['YOU'=>'me']);
		Locale::_()->options['locale_lang_final']='en_US';
		__l("BBB");
		
		////////////////
		
		MyLocale::_(new MyLocale())->init([
			'locale_lang_detect_mode'=>['NoExists'],
		]);
		
		SuperGlobal::DefineSuperGlobalContext();
		MyLocale::_(new MyLocale())->init([
			//'locale_lang_detect_mode'=>['NoExists'],
		]);
		SuperGlobal::_()->_SERVER['HTTP_ACCEPT_LANGUAGE']='zh-CN,zh;q=0.9,en;q=0.8';
		MyLocale::_(new MyLocale())->init([
			//'locale_lang_detect_mode'=>['NoExists'],
		]);
		SuperGlobal::_()->_SERVER['HTTP_ACCEPT_LANGUAGE']='';
		MyLocale::_(new MyLocale())->init([
			//'locale_lang_detect_mode'=>['NoExists'],
		]);
		
		putenv('LANG=zh_CN.UTF-8');
		MyLocale::_(new MyLocale())->init([
			//'locale_lang_detect_mode'=>['NoExists'],
		]);
		
        \LibCoverage\LibCoverage::End();
    }
}