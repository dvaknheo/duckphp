<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\Pager;
use DuckPhp\App as DuckPhp;

class PagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Pager::class);
        
        $options=[
            'url'=>'/user',
        ];
        //Pager::G()->init($options, $context=null);
        Pager::G()->render(123,$options);
        ///////////////
        Pager::G()->current();
        
        $options['url']='/a{page}';
        Pager::G()->render(123,$options);
        //
        $options['page_size']=1000000;
        Pager::G()->render(1,$options);
        
        $options['page_size']=3;
        $options['rewrite']=function($page){ return Pager::G()->defaultGetUrl($page);};

        for($i=1;$i<=9;$i++){
            $options['current']=$i;
        Pager::G()->render(26,$options);
        }
        
        Pager::G(new Pager());
        Pager::G()->current();
        Pager::G()->init(['url'=>'/user',],DuckPhp::G());
        Pager::SG();
        Pager::G()->getUrl(3);
        Pager::G()->pageSize(3);
        Pager::G()->pageSize();
        \MyCodeCoverage::G()->end(Pager::class);
        $this->assertTrue(true);
        /*
        Pager::G()->SG();
        Pager::G()->_SG();
        Pager::G()->Current();
        Pager::G()->G()->render($total, $options=[]);
        Pager::G()->_current();
        
        Pager::G()->getUrl($page);
        Pager::G()->defaultGetUrl($page);
        Pager::G()->_render($total, $options=[]);
        //*/
    }
}
