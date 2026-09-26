<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * To enable this command class, uncomment the following application option:
 *   'cmd' => [CommandAction::class => true]
 *
 * Provides a sample CLI command. Run `php bin/cli.php hello` to execute
 * CommandAction::_()->command_hello().
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\SingletonTrait;

class CommandAction
{
    use SingletonTrait;

    /**
     * Print a "hello world" message.
     */
    public function command_hello()
    {
        echo "hello world";
    }
}
