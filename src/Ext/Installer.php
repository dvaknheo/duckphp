<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class Installer extends ComponentBase
{
    public $options = [
        'src' => '',
        'dest' => '',
        'force'=>true,
        'autoload_file'=>'vendor/autoload.php',
        'verbose'=>false,
    ];
    public function init($options)
    {
        $this->options = array_replace_recursive($this->options, $options);
        return $this;
    }
    public function run()
    {
        $source = __DIR__ .'/../template';
        $dest = $this->options['dest'];
        $this->dumpDir($source, $dest, $this->options['force'], $this->options['full']);
    }
    public function dumpDir($source, $dest, $force = false)
    {
        $source = rtrim(realpath($source), '/').'/';
        $dest = rtrim(realpath($dest), '/').'/';
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
                return;
            }
        }
        echo "Copying file...\n";
        
        $flag = $this->createDirectories($dest, $files);
        if (!$flag) {
            return;
        }
        
        foreach ($files as $file => $short_file_name) {
            $dest_file = $dest.$short_file_name;
            $data = file_get_contents($file);
            $data = $this->filteText($data, $is_in_full, $short_file_name);
            $flag = file_put_contents($dest_file, $data);
            
            if ($this->options['verbose']) {
                echo $dest_file;
                echo "\n";
            }
            //decoct(fileperms($file) & 0777);
        }
        copy($source.'config/setting.sample.php',$dest.'config/setting.php');
        echo  "\nDone.\n";
    }
    protected function checkFilesExist($source, $dest, $files)
    {
        foreach ($files as $file => $short_file_name) {
            if (!$this->options['full']) {
                if (substr($short_file_name, 0, strlen('public/full/')) === 'public/full/') {
                    continue;
                }
            }
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
            $blocks = explode('/', $short_file_name);
            array_pop($blocks);
            $full_dir = $dest;
            foreach ($blocks as $t) {
                $full_dir .= DIRECTORY_SEPARATOR.$t;
                if (!is_dir($full_dir)) {
                    $flag = mkdir($full_dir);
                    if (!$flag) {
                        echo "create file failed $full_dir \n";
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    protected function filteText($data, $is_in_full, $short_file_name)
    {
        $autoload_file = $this->options['autoload_file'];
        $data = $this->changeHeadFile($data, $short_file_name, $autoload_file);
        
        if (!$is_in_full) {
            $data = $this->filteMacro($data, $is_in_full);
            $data = $this->filteNamespace($data, $this->options['namespace']);
        }
        return $data;
    }
    protected function filteMacro($data, $is_in_full)
    {
        $data = preg_replace('/^.*?@DUCKPHP_DELETE.*?$/m', '', $data);
        if (!$is_in_full) {
            $data = preg_replace('/^.*?@DUCKPHP_KEEP_IN_FULL.*?$/m', '', $data);
        }
        return $data;
    }
    protected function filteNamespace($data, $namespace)
    {
        if ($namespace === 'LazyToChange') {
            return $data;
        }
        $str_header = "\$namespace = '$namespace';";
        $data = preg_replace('/^.*?@DUCKPHP_NAMESPACE.*?$/m', $str_header, $data);
        $data =str_replace("LazyToChange\\","{$namespace}\\" ,$data);
        
        return $data;
    }
    protected function changeHeadFile($data, $short_file_name,$autoload_file)
    {
        $level = substr_count($short_file_name, '/');
        $subdir = str_repeat('../', $level);
        $str_header = "require_once(__DIR__.'/{$subdir}{$autoload_file'});";
        $data = preg_replace('/^.*?@DUCKPHP_HEADFILE.*?$/m', $str_header, $data);
        return $data;
    }
}
