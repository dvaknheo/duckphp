<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;
use DuckPhp\HttpServer\HttpServer;

class DuckPhpInstaller extends ComponentBase
{
    public $options = [
        'path' => '',
        'namespace' => '',
        
        'force' => false,
        'autoloader' => 'vendor/autoload.php',
        
        'verbose' => false,
        'help' => false,
    ];
    /**
     * create new project in current diretory.
     */
    public function command_new()
    {
        $options = Console::_()->getCliParameters();
        if ($options['help'] ?? false) {
            $this->showHelp();
            return;
        }
        $this->init([])->newProject($options);
    }
    /**
     * show this help.
     */
    public function command_help()
    {
        return $this->init([])->showHelp();
    }
    /**
     * run the demo web server
     */
    public function command_show()
    {
        return $this->init([])->runDemo();
    }
    
    public function showHelp()
    {
        echo <<<EOT
Well Come to use DuckPhp Installer ;
  help                    Show this help.
  new                     Create a project.
    --namespace <namespace>   Use another project namespace.
    --force                   Overwrite exited files.
    --verbose                 Show Progress
    --autoloadfile <path>     Use another autoload file.
    --path <path>             Copy project file to here.
  show                    Show the code demo
    --port <port>             Use anothe port

EOT;
    }
    protected function getNameSpaceByComposer(string $path): string
    {
        $path = rtrim(''.realpath($path), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $file = $path.'composer.json';
        if (!is_file($file)) {
            return '';
        }
        
        $data = json_decode(''.file_get_contents($file), true);
        $psr = $data['autoload']['psr-4'] ?? [];
        $psr = array_flip($psr);
        $namespace = $psr['src/'] ?? $psr['src'] ?? '';
        return $namespace;
    }
    protected function getNamespaceByConsole(): string
    {
        $default = ['namespace' => 'Demo'];
        $input = Console::_()->readLines($default, "enter your namespace[{namespace}]\n");
        return $input['namespace'];
    }
    public function newProject($options = [])
    {
        $namespace = $options['namespace'] ?? true;
        if (empty($namespace) || $namespace === true) {
            $namespace = $this->getNameSpaceByComposer($options['path']);
            if (!$namespace) {
                $namespace = $this->getNamespaceByConsole();
            }
        }
        
        $this->options = array_merge($this->options, $options);
        $this->options['namespace'] = $namespace;
        $source = __DIR__ .'/../../skeleton';
        $dest = $this->options['path'];
        
        $this->dumpDir($source, $dest, $this->options['force']);
    }

    public function runDemo()
    {
        $source = __DIR__ .'/../../template';
        $options = [
            'path' => $source,
        ];
        $options = Console::_()->getCliParameters();
        $options['path'] = $source;
        if (empty($options['port']) || $options['port'] === true) {
            $options['port'] = '8080';
        }
        if (!empty($options['http_server'])) {
            /** @var string */
            $class = str_replace('/', '\\', $options['http_server']);
            HttpServer::_($class::_());
        }
        HttpServer::RunQuickly($options);
    }
    protected function dumpDir(string $source, string $dest, bool $force = false): void
    {
        @mkdir($dest);
        $source = rtrim(''.realpath($source), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $dest = rtrim(''.realpath($dest), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $t_files = \iterator_to_array($iterator, false);
        $files = [];
        
        foreach ($t_files as $file) {
            $short_file_name = substr($file, strlen($source));
            $files[$file] = $short_file_name;
        }
        
        if (!$force) {
            $flag = $this->checkFilesExist($source, $dest, $files);
            if (!$flag) {
                return; // @codeCoverageIgnore
            }
        }
        echo "Copying file...\n";
        
        $flag = $this->createDirectories($dest, $files);
        if (!$flag) {
            return; // @codeCoverageIgnore
        }
        $is_in_full = false;
        
        foreach ($files as $file => $short_file_name) {
            $dest_file = $dest.$short_file_name;
            $data = file_get_contents(''.$file);
            $data = $this->filteText($data, $is_in_full, $short_file_name);
            $flag = file_put_contents($dest_file, $data);
            
            if ($this->options['verbose']) {
                echo $dest_file;
                echo "\n";
            }
            //decoct(fileperms($file) & 0777);
        }
        //copy($source.'config/setting.sample.php', $dest.'config/setting.php');
        echo  "\nDone.\n";
    }
    protected function checkFilesExist(string $source, string $dest, array $files): bool
    {
        foreach ($files as $file => $short_file_name) {
            $dest_file = $dest.$short_file_name;
            if (is_file($dest_file)) {
                echo "file exists: $dest_file \n";
                echo "use --force to overwrite existed files \n";
                return false;
            }
        }
        return true;
    }
    protected function createDirectories(string $dest, array $files): bool
    {
        foreach ($files as $file => $short_file_name) {
            // mkdir.
            $blocks = explode(DIRECTORY_SEPARATOR, $short_file_name);
            array_pop($blocks);
            $full_dir = $dest;
            foreach ($blocks as $t) {
                $full_dir .= DIRECTORY_SEPARATOR.$t;
                if (!is_dir($full_dir)) {
                    $flag = mkdir($full_dir);
                    if (!$flag) {                               // @codeCoverageIgnore
                        echo "create file failed: $full_dir \n";// @codeCoverageIgnore
                        return false;   // @codeCoverageIgnore
                    }
                }
            }
        }
        return true;
    }
    
    protected function filteText(string $data, bool $is_in_full, string $short_file_name): string
    {
        $autoload_file = $this->options['autoloader'];
        $data = $this->changeHeadFile($data, $short_file_name, $autoload_file);
        
        if (!$is_in_full) {
            $data = $this->filteMacro($data);
            $data = $this->filteNamespace($data, $this->options['namespace']);
        }
        return $data;
    }
    protected function filteMacro(string $data): string
    {
        $data = preg_replace('/^.*?@DUCKPHP_DELETE.*?$/m', '', $data);
        return $data;
    }

    protected function filteNamespace(string $data, string $namespace): string
    {
        //if ($namespace === 'ProjectNameTemplate' || $namespace === '') {
        //    return $data;
        //}
        $str_header = "\$namespace = '$namespace';";
        $data = preg_replace('/^.*?@DUCKPHP_NAMESPACE.*?$/m', $str_header, $data);
        $data = str_replace("YourProjectName\\", "{$namespace}\\", $data);
        
        return $data;
    }
    protected function changeHeadFile(string $data, string $short_file_name, string $autoload_file): string
    {
        $level = substr_count($short_file_name, DIRECTORY_SEPARATOR);
        $subdir = str_repeat('../', $level);
        $str_header = "require_once(__DIR__.'/{$subdir}{$autoload_file}');";
        $data = preg_replace('/^.*?@DUCKPHP_HEADFILE.*?$/m', $str_header, $data);
        return $data;
    }
    

    /*
    protected function genProjectName()
    {
        $str = "abcdefghijklmnopqrstuvwxyz";
        $l = strlen($str)-1;
        $x = mt_rand(0,$l);
        $ret = 'Project'.DATE("ymd").'_'.$str[mt_rand(0,$l)].$str[mt_rand(0,$l)].$str[mt_rand(0,$l)].$str[mt_rand(0,$l)];
        return $ret;
    }
    protected function detectedClass($path)
        {
            $composer_file = $path.'/composer.json';
            $data = json_decode(file_get_contents($composer_file),true);
            $psrs = $data['autoload']['psr-4'] ?? [];
            foreach($psrs as $k => $v){
                $ns = $k;
                break;
            }
            if(empty($ns)){
                return '';
            }
            $class = $ns . 'System\App';
            if(!class_exists($class)){
                return '';
            }
            return $class;
        }
    */
}
