<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\DuckPhp;
use DuckPhp\Foundation\ExceptionReporterTrait;
use DuckPhp\Core\ThrowOnTrait;

class ExceptionReporterTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExceptionReporterTrait::class);
        
        $options=[
            'namespace' => 'tests\DuckPhp\Foundation',
            'namespace_controller' => '\tests\DuckPhp\Foundation',
            'exception_reporter' => MyExceptionReporter::class,
            'handle_all_exception'=>false,
            'controller_class_postfix'=>'Controller',
            'controller_method_prefix'=>'action_',
            'cli_mode'=>false,
            'is_debug'=>true,
        ];
        DuckPhp::G()->init($options);

        $_SERVER['PATH_INFO']='/first';
        DuckPhp::G()->run();
        $_SERVER['PATH_INFO']='/second';
        DuckPhp::G()->run();
        $_SERVER['PATH_INFO']='/third';
        DuckPhp::G()->run();
        \LibCoverage\LibCoverage::End();
    }
}
class MainController
{
    public function action_first()
    {
        throw new TheFirstException("xx");
    }
    public function action_second()
    {
        throw new \Exception("xx");
    }
    public function action_third()
    {
        throw new TheSecondException("xx");
    }
}
class MyExceptionReporter
{
    use ExceptionReporterTrait;
    public function onTheFirstException($ex)
    {
        //
    }
    public function defaultExceptionX($ex)
    {
        var_dump(get_class($ex));
    }
}
class TheFirstException extends \Exception
{
    use ThrowOnTrait;
}
class TheSecondException extends \Exception
{
    use ThrowOnTrait;
}