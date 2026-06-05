<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Locale;
use DuckPhp\Component\RedisManager;

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
        ]);
        __l("Hello {YOU}", ['YOU'=>'me']);
		Locale::_()->options['locale_lang_final']=null;
		__l("Hello {YOU}", ['YOU'=>'me']);
		
		Locale::_()->options['locale_lang_final']='NoExists';
		__l("Hello {YOU}", ['YOU'=>'me']);
		Locale::_()->options['locale_lang_final']='en_US';
		__l("BBB");
		
        \LibCoverage\LibCoverage::End();
    }
}