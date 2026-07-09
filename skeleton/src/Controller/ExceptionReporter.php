<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * To enable exception reporting, uncomment these application options in
 * YourProjectName\System\App:
 *   'exception_reporter'       => ExceptionReporter::class,
 *   'exception_for_project'    => ProjectException::class,
 *   'exception_for_business'   => BusinessException::class,
 *   'exception_for_controller' => ControllerException::class
 *
 * Handles exceptions that the application wants to report explicitly.
 * `exception_reporter` is only invoked for exceptions matching `exception_for_project`.
 * Since `BusinessException` and `ControllerException` extend `ProjectException`,
 * they are also routed to the reporter.
 * Helper::BusinessThrowOn() calls onBusinessException().
 * Helper::ControllerThrowOn() calls onControllerException().
 * Any other exception is shown by the error_500 view.
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\ExceptionReporterTrait;

class ExceptionReporter
{
    use ExceptionReporterTrait;

    public function onBusinessException($ex)
    {
        var_dump(__METHOD__);
    }

    public function onControllerException($ex)
    {
        var_dump(__METHOD__);
    }
}
