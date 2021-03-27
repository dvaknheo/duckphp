<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class Installer extends ComponentBase
{
    public $options = [
        'path' => '',
        'namespace' => '',
        
        'force' => false,
        'autoloader' => 'vendor/autoload.php',
        
        'verbose' => false,
        'help' => false,
    ];
    public static function RunQuickly($options)
    {
        return static::G()->init($options)->run();
    }
    public function init(array $options, $context = null)
    {
        $this->options = array_replace_recursive($this->options, $options);
        return $this;
    }
    public function run()
    {
        if ($this->options['help']) {
            $this->showHelp();
            return;
        }
        $source = __DIR__ .'/../../template';
        $dest = $this->options['path'];

        $this->dumpDir($source, $dest, $this->options['force']);
    }
    protected function dumpDir($source, $dest, $force = false)
    {
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
        copy($source.'config/setting.sample.php', $dest.'config/setting.php');
        echo  "\nDone.\n";
    }
    protected function checkFilesExist($source, $dest, $files)
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
    protected function createDirectories($dest, $files)
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
    
    protected function filteText($data, $is_in_full, $short_file_name)
    {
        $autoload_file = $this->options['autoloader'];
        $data = $this->changeHeadFile($data, $short_file_name, $autoload_file);
        
        if (!$is_in_full) {
            $data = $this->filteMacro($data);
            $data = $this->filteNamespace($data, $this->options['namespace']);
        }
        return $data;
    }
    protected function filteMacro($data)
    {
        $data = preg_replace('/^.*?@DUCKPHP_DELETE.*?$/m', '', $data);
        return $data;
    }
    protected function filteNamespace($data, $namespace)
    {
        if ($namespace === 'LazyToChange' || $namespace === '') {
            return $data;
        }
        $str_header = "\$namespace = '$namespace';";
        $data = preg_replace('/^.*?@DUCKPHP_NAMESPACE.*?$/m', $str_header, $data);
        $data = str_replace("LazyToChange\\", "{$namespace}\\", $data);
        
        return $data;
    }
    protected function changeHeadFile($data, $short_file_name, $autoload_file)
    {
        $level = substr_count($short_file_name, DIRECTORY_SEPARATOR);
        $subdir = str_repeat('../', $level);
        $str_header = "require_once(__DIR__.'/{$subdir}{$autoload_file}');";
        $data = preg_replace('/^.*?@DUCKPHP_HEADFILE.*?$/m', $str_header, $data);
        return $data;
    }
    
    protected function showHelp()
    {
        echo <<<EOT
Well Come to use DuckPhp Installer ;
  --help                    Show this help.
  --namespace <namespace>   Use another project namespace.
  --force                   Overwrite exited files.
  --verbose                 Show Progress
  --autoloadfile <path>     Use another autoload file.
  --path <path>             Copy project file to here.
EOT;
        //--full                    Use The demo template
    }
}
