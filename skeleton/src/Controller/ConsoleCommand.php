<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * To enable this command class, uncomment the following application option:
 *   'cli_command_classes' => [ConsoleCommand::class]
 *
 * Provides a sample CLI command. Run `php ./cli.php hello` to execute
 * ConsoleCommand::_()->command_hello().
 */
namespace YourProjectName\Controller;

class ConsoleCommand extends Base
{
	public function __construct()
	{
		// Must override parent
	}
    
    /**
     * Print a "hello world" message.
     */
    public function command_hello()
    {
        echo "hello world";
    }
}
