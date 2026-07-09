<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * To enable project-aware exception reporting, uncomment the application option:
 *   'exception_for_project' => ProjectException::class
 *
 * Base exception class for project-specific errors. Uses SimpleExceptionTrait
 * to keep exception handling simple and consistent across the application.
 * Usually you do not need to change this file.
 */
namespace YourProjectName\System;

use DuckPhp\Foundation\SimpleExceptionTrait;

class ProjectException
{
    use SimpleExceptionTrait;
}
