<?php
namespace tests\DNMVCS\Core\Helper;

use DNMVCS\Core\Helper\ServiceHelper;

class ServiceHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ServiceHelper::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ServiceHelper::class);
        $this->assertTrue(true);
        /*
        ServiceHelper::G()->Setting($key);
        ServiceHelper::G()->Config($key, $file_basename='config');
        ServiceHelper::G()->LoadConfig($file_basename);
        //*/
    }
}
