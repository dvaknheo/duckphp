<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * To enable project-aware exception reporting, uncomment the application option:
 *   'exception_for_project' => ProjectException::class
 *
 * Base exception class for project-specific errors. It extends \Exception
 * (so it can actually be thrown) and uses ExceptionTrait for the ThrowOn()
 * guard style. Do not extend DuckPhpSystemException: that class only means
 * "the framework itself is broken".
 * Usually you do not need to change this file.
 */
namespace YourProjectName\System;

use DuckPhp\Foundation\ExceptionTrait;

class ProjectException extends \Exception
{
    use ExceptionTrait;
}
