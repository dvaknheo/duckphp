<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\ModelHelper;
use DuckPhp\App as DuckPhp;
class ModelHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ModelHelper::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($options);
        try {
        ModelHelper::DB($tag=null);
        } catch(\Throwable $ex) {
        }
        try {
        ModelHelper::DB_W();
        } catch(\Throwable $ex) {
        }
        try {
        ModelHelper::DB_R();
        } catch(\Throwable $ex) {
        }
        
        \MyCodeCoverage::G()->end(ModelHelper::class);
        $this->assertTrue(true);

    }
}
