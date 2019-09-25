<?php 
namespace tests\DNMVCS\Ext;
use DNMVCS\Ext\DBReusePoolProxy;

class DBReusePoolProxyTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBReusePoolProxy::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(DBReusePoolProxy::class);
        $this->assertTrue(true);
        /*
        DBReusePoolProxy::G()->init($options=[], $context=null);
        DBReusePoolProxy::G()->setDBHandler($db_create_handler, $db_close_handler=null);
        DBReusePoolProxy::G()->getObject($db_config, $tag);
        DBReusePoolProxy::G()->reuseObject($db, $tag);
        DBReusePoolProxy::G()->onCreate($db_config, $tag);
        DBReusePoolProxy::G()->checkException();
        DBReusePoolProxy::G()->onClose($db, $tag);
        DBReusePoolProxy::G()->proxy($dbm);
        //*/
    }
}
