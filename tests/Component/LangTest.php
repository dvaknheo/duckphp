<?php 
namespace tests\DuckPhp\Component;

use DuckPhp\DuckPhp;
use DuckPhp\Core\App;

use DuckPhp\Component\Lang;
use DuckPhp\Component\Configer;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\SuperGlobal;;
use DuckPhp\Core\PhaseContainer;

class LangApp extends DuckPhp
{

}
class MyLang extends Lang
{
    public function manual_detectLanguage()
    {
        $this->options['lang_detect_mode']=['NoExists'];
        $this->detectLanguage();
        
        $this->options['lang_detect_mode']=['default'];
        $this->detectLanguage();
    }
    
}
class LangTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Lang::class);
		$path=\LibCoverage\LibCoverage::G()->getClassTestPath(Lang::class);

		DuckPhp::_(new DuckPhp())->init([
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
		__l("NoExists");
		Lang::_()->options['lang_final']=null;
		__l("Hello {YOU}", ['YOU'=>'me']);
		
		Lang::_()->options['lang_final']='NoExists';
		__l("Hello {YOU}", ['YOU'=>'me']);
		Lang::_()->options['lang_final']='en_US';
		__l("BBB");
		// fallback：有 fallback 且翻译未命中 → 用 fallback
		$this->assertSame('FALLBACK_TEXT', __l("NoExists", [], 'FALLBACK_TEXT'));
		// __langtext：部分匹配 {{key|fallback}}
		$this->assertSame('Use foo mode', __langtext("Use {{command.foo|foo}} mode"));
		// __langtext：无 fallback {{key}} → 未命中返回 key 本身
		$this->assertSame('command.bar', __langtext("{{command.bar}}"));
		// __langtext：无占位符原样返回
		$this->assertSame('plain text', __langtext('plain text'));
		// __langtext：fallback 含 {word} 块
		$this->assertSame('just {myword}', __langtext("{{some_key|just {myword}}}"));
		// Lang::replaceText：部分匹配混排
		$this->assertSame('Use foo mode', Lang::_()->replaceText('Use {{command.foo|foo}} mode'));
		// replaceText：{{key}} 无 fallback → 未命中返回 key 本身
		$this->assertSame('command.bar', Lang::_()->replaceText('{{command.bar}}'));
		// replaceText：无占位符原样
		$this->assertSame('plain text', Lang::_()->replaceText('plain text'));
		
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
PhaseContainer::RestAllContainerForTesting();

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
        echo __l("IMNOEXITSADSF");
        // replaceText：翻译命中（lang_simple_mode_only_sentences，lang_final=zh_CN）
        Lang::_()->options['lang_final'] = 'zh_CN';
        $this->assertSame('zh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CN', Lang::_()->replaceText('{{AAA|fallback}}'));
        $this->assertSame('fallback', Lang::_()->replaceText('{{NOEXIT|fallback}}'));
        
        MyLang::_()->manual_detectLanguage();
        
        // importDefaultSentences: default fallback when lang_final is null
        Lang::_()->options['lang_final'] = null;
        Lang::_()->importDefaultSentences(['webinstaller.h1' => 'DuckPhp Web Installer', 'only_default' => 'Only Default']);
        $this->assertSame('DuckPhp Web Installer', Lang::_()->language('webinstaller.h1', [], 'FALLBACK_TEXT'));
        $this->assertSame('Only Default', Lang::_()->language('only_default', [], 'FALLBACK_TEXT'));
        // translation (real sentences) wins over default sentences
        Lang::_()->options['lang_final'] = 'zh_CN';
        $this->assertSame('zh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CNzh_CN', Lang::_()->language('AAA', [], 'FALLBACK_TEXT'));
        // default sentences win over fallback
        Lang::_()->options['lang_final'] = null;
        $this->assertSame('DuckPhp Web Installer', Lang::_()->language('webinstaller.h1', [], 'FALLBACK_TEXT'));
        // missing key -> fallback
        $this->assertSame('FALLBACK_TEXT', Lang::_()->language('no_such_key', [], 'FALLBACK_TEXT'));
        // importDefaultSentences merges (accumulates) and returns $this
        Lang::_()->importDefaultSentences(['another' => 'Another']);
        $this->assertSame('Another', Lang::_()->language('another'));
        $this->assertSame(Lang::_(), Lang::_()->importDefaultSentences(['x' => 'y']));
        
        \LibCoverage\LibCoverage::End();
    }
}