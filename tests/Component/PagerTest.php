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
        Pager::G()->init($options, DuckPhp::G());
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
        Pager::G()->getUrl(3);
        Pager::G()->pageSize(3);
        Pager::G()->current(1);
        Pager::G()->pageSize();
        Pager::G()->isInited();
        \LibCoverage\LibCoverage::End();
    }
}
