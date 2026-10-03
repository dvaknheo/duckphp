<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\Pager;
use DuckPhp\DuckPhp;

class PagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Pager::class);
        
        $options=[
            //'url'=>'/user',
        ];
        Pager::_()->init($options, DuckPhp::_());
        Pager::_()->render(123,$options);
        ///////////////
        Pager::_()->current();
        
        $options['url']='/a{page}';
        Pager::_()->render(123,$options);
        //
        $options['page_size']=1000000;
        Pager::_()->render(1,$options);
        
        $options['page_size']=3;
        $options['rewrite']=function($page){ return Pager::_()->defaultGetUrl($page);};

        for($i=1;$i<=9;$i++){
            $options['current']=$i;
            Pager::_()->render(26,$options);
        }
        
        Pager::_(new Pager());
        Pager::_()->current();
        Pager::_()->init(['url'=>'/user',],DuckPhp::_());
        Pager::_()->getUrl(3);
        Pager::_()->pageSize(3);
        Pager::_()->current(1);
        Pager::_()->pageSize();
        Pager::_()->isInited();
        
        Pager::PageNo();
        Pager::PageWindow();
        Pager::PageHtml(26,$options);

        // 回归:page 为空串或非数字时,不能因为 getDefaultPageNo() 的 int 返回类型抛
        // TypeError(整页 500)。用户手输 ?page=abc、或 rewrite 规则拼出 page= 都会命中。
        foreach ([['', 1], ['abc', 1], ['3', 3]] as [$page, $expected]) {
            $_GET['page'] = $page;
            if (\defined('__SUPERGLOBAL_CONTEXT')) {
                (__SUPERGLOBAL_CONTEXT)()->_GET['page'] = $page;
            }
            Pager::_(new Pager());
            Pager::_()->init(['url' => '/user'], DuckPhp::_());
            $this->assertSame($expected, Pager::_()->current(), "page=[{$page}] 时应为 {$expected}");
        }
        unset($_GET['page']);

        \LibCoverage\LibCoverage::End();
    }
}
