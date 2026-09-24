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

        ////[[[[ saveExtOptions()：root 相的 loader 没开 data_file_enable → 直接抛 DuckPhpSystemException
        // 注意：这里的 getRoot() 返回的是「root 相位里的 ExtOptionsLoader 组件」而不是 App，
        // 所以它读的是组件自己的 data_file_enable（默认 true），要显式关掉才会走到抛异常那行。
        PhaseContainer::RestAllContainerForTesting();
        $opts3 = [
            'path' => $path,
        ];
        DuckPhpEOL::_(new DuckPhpEOL);
        DuckPhpEOL::_()->init($opts3);
        ExtOptionsLoader::_()->options['data_file_enable'] = false;
        try {
            ExtOptionsLoader::_()->saveExtOptions(['x' => 1]);
            $this->fail('root 没开 data_file_enable 时保存扩展选项应抛异常');
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            $this->assertStringContainsString("must enable 'data_file_enable' in root!", $ex->getMessage());
        }
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
