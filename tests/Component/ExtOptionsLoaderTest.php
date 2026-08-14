<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Core\PhaseContainer;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\DuckPhp;
class ExtOptionsLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExtOptionsLoader::class);
        $path=\LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        @unlink($path.'runtime/DuckPhpData.config.json');
        clearstatcache();
        
        $options= [];
        
        
        $options['path'] = $path;
        $options['app'] = [
            DuckPhpEOLChild::class =>[
                'data_file_enable' => true,
                'data_file_bump_prefix_keys'=>['noenable_'=>false],
            ],
        ];
        DuckPhpEOL::_()->init($options);
        $old_phase = DuckPhpEOL::Phase();
        DuckPhpEOL::_()->toChildPhase(DuckPhpEOLChild::class);
        
        ExtOptionsLoader::_()->saveExtOptions(['xdata'=>DATE(DATE_ATOM),"installed"=>"a","redis_x"=>"b"]);
            DuckPhpEOLChild::_(new DuckPhpEOLChild());
            ExtOptionsLoader::_(new ExtOptionsLoader());       
        DuckPhpEOL::Phase($old_phase);
        
        DuckPhpEOL::_(new DuckPhpEOL);
PhaseContainer::RestAllContainerForTesting();
        ExtOptionsLoader::_(new ExtOptionsLoader());
        DuckPhpEOL::_()->init($options);
        ////[[[[
        
PhaseContainer::RestAllContainerForTesting();
$options['app'] = [
            DuckPhpEOLChild2::class =>[
                'data_file_enable' => true,
            ],
            DuckPhpEOLChild::class =>[
                'data_file_enable' => true,
                'data_file_bump_allowed' => false,
            ],
        ];
        DuckPhpEOL::_(new DuckPhpEOL);
       
        ExtOptionsLoader::_(new ExtOptionsLoader());
        
        DuckPhpEOL::_()->init($options);
        ////]]]]
        
        ////[[[[ 覆盖 loadAllOptions / get_ext_options_file / bump_allowed=false 分支
        $file = $path.'runtime/DuckPhpData.config.json';
        
        // root + data_file_enable=true → onPrepare 触发 ExtOptionsLoader::init → loadAllOptions + bumpOptions
        PhaseContainer::RestAllContainerForTesting();
        @unlink($file); clearstatcache();
        file_put_contents($file, json_encode(['' => ['installed' => 'root_val']]));
        $opts = [
            'path' => $path,
            'data_file_enable' => true,
        ];
        DuckPhpEOL::_(new DuckPhpEOL);
        ExtOptionsLoader::_(new ExtOptionsLoader());
        DuckPhpEOL::_()->init($opts);
        $this->assertSame('root_val', DuckPhpEOL::_()->options['installed'] ?? null);
        
        // data_file_bump_allowed=false → bumpOptions 的 return 分支（74 行）
        PhaseContainer::RestAllContainerForTesting();
        @unlink($file); clearstatcache();
        file_put_contents($file, json_encode(['' => ['no_bump' => 'x']]));
        $opts2 = [
            'path' => $path,
            'data_file_enable' => true,
            'data_file_bump_allowed' => false,
        ];
        DuckPhpEOL::_(new DuckPhpEOL);
        ExtOptionsLoader::_(new ExtOptionsLoader());
        DuckPhpEOL::_()->init($opts2);
        $this->assertArrayNotHasKey('no_bump', DuckPhpEOL::_()->options);
        @unlink($file); clearstatcache();
        ////]]]]
        
        
        @unlink($path.'runtime/DuckPhpData.config.json');
        clearstatcache();
        \LibCoverage\LibCoverage::End();
    }
}
class DuckPhpEOL extends DuckPhp
{
    public $options =[
        'name' => 'X',
    ];
}
class DuckPhpEOLChild extends DuckPhp
{
    public $options =[
        'name' => 'DuckPhpEOLChild',
    ];
}
class DuckPhpEOLChild2 extends DuckPhp
{
    public $options =[
        'name' => 'DuckPhpEOLChild2',
    ];
}
