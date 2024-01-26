<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\DuckPhp;
class ExtOptionsLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExtOptionsLoader::class);
        $path=\LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        var_dump($path);
        $options= [];
        $options['path'] = $path;
        $options['ext_options_file']='config/ExtOptionsLoader.config.php';
        $options['ext'] = [
            DuckPhpEOLChild::class =>[
                'path'=> 'no_exists',
                'ext_options_file_enable' => true,
                'ext_options_file' =>'no_exists222',
            ],
        ];
        DuckPhpEOL::_()->init($options);
        DuckPhpEOLChild::Phase(DuckPhpEOLChild::class);
        ExtOptionsLoader::_()->loadExtOptions(true, DuckPhpEOLChild::class);
        DuckPhpEOLChild::_()->install(['d'=>DATE(DATE_ATOM)]);
        ExtOptionsLoader::_()->loadExtOptions(false, DuckPhpEOLChild::class);
        ExtOptionsLoader::_()->saveExtOptions(true, DuckPhpEOLChild::class);
        
        @unlink($path.'config/ExtOptionsLoader.config.php');
        \LibCoverage\LibCoverage::End();
    }
}
class DuckPhpEOL extends DuckPhp
{
}
class DuckPhpEOLChild extends DuckPhp
{
}