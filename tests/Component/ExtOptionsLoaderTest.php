<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\DuckPhp;
class ExtOptionsLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExtOptionsLoader::class);
        $path=\LibCoverage\LibCoverage::G()->getClassTestPath(ExtOptionsLoader::class);
        @unlink($path.'runtime/DuckPhpExtData.config.json');
        clearstatcache();
        
        $options= [];
        
        
        $options['path'] = $path;
        $options['app'] = [
            DuckPhpEOLChild::class =>[
                'ext_options_file_enable' => true,
            ],
        ];
        DuckPhpEOL::_()->init($options);
        $old_phase = DuckPhpEOL::Phase(DuckPhpEOLChild::class);
        ExtOptionsLoader::_()->saveData(['xdata'=>DATE(DATE_ATOM),"installed"=>"a","redis_x"=>"b"]);
            DuckPhpEOLChild::_(new DuckPhpEOLChild());
            ExtOptionsLoader::_(new ExtOptionsLoader());       
        DuckPhpEOL::Phase($old_phase);
        
        DuckPhpEOL::_(new DuckPhpEOL);
        
        ExtOptionsLoader::$all_ext_options=null;
        ExtOptionsLoader::_(new ExtOptionsLoader());
        DuckPhpEOL::_()->init($options);
        ////[[[[
        
        $options['app'] = [
            DuckPhpEOLChild2::class =>[
                'ext_options_file_enable' => true,
            ],
            DuckPhpEOLChild::class =>[
                'ext_options_file_enable' => true,
                'ext_options_allow_init_replace' => false,
            ],
        ];
        DuckPhpEOL::_(new DuckPhpEOL);
       
        ExtOptionsLoader::_(new ExtOptionsLoader());
        ExtOptionsLoader::$all_ext_options=null;
        
        DuckPhpEOL::_()->init($options);
        ////]]]]
        
        
        @unlink($path.'runtime/DuckPhpExtData.config.json');
        clearstatcache();
        \LibCoverage\LibCoverage::End();
    }
}
class DuckPhpEOL extends DuckPhp
{
}
class DuckPhpEOLChild extends DuckPhp
{
}
class DuckPhpEOLChild2 extends DuckPhp
{
}