<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * To use this exception, uncomment the application option:
 *   'exception_for_business' => BusinessException::class
 *
 * Thrown by Helper::BusinessThrowOn() to represent a business-level error.
 * Usually you do not need to change this file.
 */
namespace YourProjectName\System;

class BusinessException extends ProjectException
{
}
