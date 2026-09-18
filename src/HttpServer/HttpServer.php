<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\HttpServer;

class HttpServer
{
    public $options = [
        'host' => '127.0.0.1',
        'port' => '8080',
        'path' => '',
        'path_document' => 'public',
        'workers' => null,
        // 'docroot'
        // 'dry'
        //'background' =>true,
    ];
    protected $cli_options = [
        'help' => [
            'short' => 'h',
            'desc' => 'show this help;',
        ],
        'host' => [
            'short' => 'H',
            'desc' => 'set server host,default is 127.0.0.1',
            'required' => true,
        ],
        'port' => [
            'short' => 'P',
            'desc' => 'set server port,default is 8080',
            'required' => true,
        ],
        'docroot' => [
            'short' => 't',
            'desc' => 'document root',
            'required' => true,
        ],
        'dry' => [
            'desc' => 'dry mode, just show cmd',
        ],
        'background' => [
            'short' => 'b',
            'desc' => 'run background',
        ],
    ];
    public $pid = 0;
    /**
     * Handle of the background server process. Only used on Windows, where the server is
     * started through proc_open() because there is no POSIX shell to background it with.
     *
     * @var resource|null
     */
    protected $process;

    protected $cli_options_ex = [];
    protected $args = [];
    protected $docroot = '';

    protected $host;
    protected $port;
    protected $is_inited = false;

    protected static $_instances = [];
    //embed
    public static function _($object = null)
    {
        if (defined('__SINGLETONEX_REPALACER')) {
            return (__SINGLETONEX_REPALACER)(static::class, $object);
        }
        if ($object) {
            self::$_instances[static::class] = $object;
            return $object;
        }
        $me = self::$_instances[static::class] ?? null;
        if (null === $me) {
            $me = new static();
            self::$_instances[static::class] = $me;
        }

        return $me;
    }
    public function __construct()
    {
    }
    public static function RunQuickly($options)
    {
        return static::_()->init($options)->run();
    }

    /**
     * @param array<string, mixed> $options
     * @param object|null $context
     * @return static
     */
    public function init(array $options, ?object $context = null)
    {
        $this->options = array_replace_recursive($this->options, $options);
        $this->host = $this->options['host'];
        $this->port = $this->options['port'];
        $this->args = $this->parseCaptures($this->cli_options); // TODO remove

        $this->docroot = rtrim($this->options['path'] ?? '', '/').'/'.$this->options['path_document'];

        $this->host = $this->args['host'] ?? $this->host;
        $this->port = $this->args['port'] ?? $this->port;
        $this->docroot = $this->args['docroot'] ?? $this->docroot;

        $this->is_inited = true;

        return $this;
    }
    public function isInited(): bool
    {
        return $this->is_inited;
    }
    /**
     * @param string[] $longopts
     */
    protected function getopt(string $options, array $longopts, &$optind)
    {
        return getopt($options, $longopts, $optind); // @codeCoverageIgnore
    }
    /**
     * @param array<string, mixed> $cli_options
     * @param array<string, mixed> $cli_options
     */
    protected function parseCaptures(array $cli_options): array
    {
        $shorts_map = [];
        $shorts = [];
        $longopts = [];

        foreach ($cli_options as $k => $v) {
            $required = $v['required'] ?? false;
            $optional = $v['optional'] ?? false;
            $longopts[] = $k.($required?':':'').($optional?'::':'');
            if (isset($v['short'])) {
                $shorts[] = $v['short'].($required?':':'').($optional?'::':'');
                $shorts_map[$v['short']] = $k;
            }
        }
        $optind = null;
        $args = $this->getopt(implode('', ($shorts)), $longopts, $optind);
        $args = $args?:[];

        $pos_args = array_slice($_SERVER['argv'], $optind);

        foreach ($shorts_map as $k => $v) {
            if (isset($args[$k]) && !isset($args[$v])) {
                $args[$v] = $args[$k];
            }
        }
        $args = array_merge($args, $pos_args);
        return $args;
    }
    public function run()
    {
        $this->showWelcome();
        if (isset($this->args['help'])) {
            return $this->showHelp();
        }
        return $this->runHttpServer();
    }
    public function getPid(): int
    {
        return $this->pid;
    }
    public function close()
    {
        // Windows: there is no posix_kill(), so the server is terminated through its handle.
        if (is_resource($this->process)) {
            proc_terminate($this->process, 9); // @codeCoverageIgnore

            // reap the child, otherwise PHP waits for it on shutdown
            proc_close($this->process); // @codeCoverageIgnore
            $this->process = null; // @codeCoverageIgnore
            $this->pid = 0; // @codeCoverageIgnore
            return true; // @codeCoverageIgnore
        }
        if (!$this->pid) {
            return false;
        }
        if (static::isWindows()) {
            // A PID without a handle can only come from the outside; kill the whole tree.
            exec('taskkill /F /T /PID ' . (int)$this->pid); // @codeCoverageIgnore
            return true; // @codeCoverageIgnore
        }
        posix_kill($this->pid, 9);
        return true;
    }
    /**
     * Windows has no POSIX shell, so the two platforms need different handling.
     */
    protected static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
    protected function showWelcome(): void
    {
        echo "DuckPhp: Wellcome, for more info , use --help \n";
    }
    protected function showHelp()
    {
        $doc = "Usage :\n\n";
        echo $doc;
        foreach ($this->cli_options as $k => $v) {
            $long = $k;

            $t = $v['short'] ?? '';
            $t = $t?'-'.$t:'';
            if ($v['optional'] ?? false) {
                $long .= ' ['.$k.']';
                $t .= ' ['.$k.']';
            }
            if ($v['required'] ?? false) {
                $long .= ' <'.$k.'>';
                $t .= ' <'.$k.'>';
            }
            echo " --{$long}\t{$t}\n\t".$v['desc']."\n";
        }
        echo "Current args :\n";
        var_export($this->args);
        echo "\n";
    }
    protected function runHttpServer()
    {
        $PHP = escapeshellcmd(PHP_BINARY);
        $host = escapeshellarg((string)$this->host);
        $port = escapeshellarg((string)$this->port);
        $document_root = escapeshellarg($this->docroot);

        if (isset($this->args['background'])) {
            $this->options['background'] = true;
        }
        if ($this->options['background'] ?? false) {
            echo "DuckPhp: RunServer by PHP inner http server {$this->host}:{$this->port}\n";
        }
        $cmd = "$PHP -S $host:$port -t $document_root ";
        if (!empty($this->options['workers'])) {
            // PHP 7.4+ built-in server multi-worker, supports internal loopback requests (e.g. RPC demo)
            $cmd = 'PHP_CLI_SERVER_WORKERS=' . (int)$this->options['workers'] . ' ' . $cmd;
        }
        // Windows has no POSIX shell: there, "> /dev/null 2>&1 & echo $!;" makes cmd.exe write
        // "The system cannot find the path specified." to stderr and return a bogus PID, so the
        // server is started without any shell on that platform.
        if (static::isWindows()) {
            return $this->runHttpServerOnWindows(); // @codeCoverageIgnore
        }
        if (isset($this->args['dry'])) {
            echo $cmd;
            echo "\n";
            return;
        }
        if ($this->options['background'] ?? false) {
            $cmd .= ' > /dev/null 2>&1 & echo $!; ';
            $pid = exec($cmd);
            $this->pid = (int)$pid;
            return $pid;
        }
        echo "DuckPhp running at : http://{$this->host}:{$this->port}/ \n"; // @codeCoverageIgnore
        return system($cmd); // @codeCoverageIgnore
    }
    // @codeCoverageIgnoreStart
    /**
     * Start the built-in server on Windows without going through a shell.
     *
     * The command is handed to proc_open() as an array, so no cmd.exe is involved: the child
     * cannot write to our stderr, and proc_get_status() reports the real PID. The standard
     * streams are redirected to the null device instead of pipes, otherwise the server would
     * block as soon as a pipe buffer fills up.
     *
     * @return int|false|void
     */
    protected function runHttpServerOnWindows()
    {
        $command = [PHP_BINARY, '-S', $this->host . ':' . $this->port, '-t', $this->docroot];

        if (isset($this->args['dry'])) {
            echo implode(' ', $command);
            echo "\n";
            return;
        }
        if (!($this->options['background'] ?? false)) {
            echo "DuckPhp running at : http://{$this->host}:{$this->port}/ \n"; // @codeCoverageIgnore
            $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, null, $this->serverEnvironment()); // @codeCoverageIgnore
            return is_resource($process) ? proc_close($process) : false; // @codeCoverageIgnore
        }
        $process = proc_open(
            $command,
            [0 => ['file', 'NUL', 'r'], 1 => ['file', 'NUL', 'a'], 2 => ['file', 'NUL', 'a']],
            $pipes,
            null,
            $this->serverEnvironment()
        );
        if (!is_resource($process)) {
            throw new \RuntimeException('DuckPhp: unable to start the PHP built-in server');
        }
        $this->process = $process;
        /** @var array{pid:int} $status */
        $status = proc_get_status($process);
        $this->pid = (int)$status['pid'];
        // PHP waits for a live proc_open() child when the script ends, so always reap it.
        register_shutdown_function([$this, 'close']);
        return $this->pid;
    }
    /**
     * Environment for the server process. On Windows PHP_CLI_SERVER_WORKERS has to be an
     * environment variable: the POSIX variant puts it in front of the shell command, where
     * cmd.exe would read it as the name of the program to run.
     *
     * @return array<string,string>
     */
    protected function serverEnvironment(): array
    {
        $env = getenv();
        if (!is_array($env)) {
            $env = [];
        }
        if (!empty($this->options['workers'])) {
            $env['PHP_CLI_SERVER_WORKERS'] = (string)(int)$this->options['workers'];
        }
        return $env;
    } // @codeCoverageIgnoreEnd
}
