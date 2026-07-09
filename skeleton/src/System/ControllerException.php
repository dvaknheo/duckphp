<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * To use this exception, uncomment the application option:
 *   'exception_for_controller' => ControllerException::class
 *
 * Thrown by Helper::ControllerThrowOn() to represent a controller-level error.
 * Usually you do not need to change this file.
 */
namespace YourProjectName\System;

class ControllerException extends ProjectException
{
}
