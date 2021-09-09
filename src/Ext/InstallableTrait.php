<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

trait InstallableTrait
{
    protected function getOptionsKeyPrefixForNamespace()
    {
        $string = $this->options['namespace'];
        
        //CamelCase to Underscore
        $string = preg_replace('/(?<=\\w)(?=[A-Z])/', "_$1", $string);
        $string = strtolower($string);
        $string = str_replace('\\', '-', $string).'_';
        return $string;
    }
    public function isInstalled()
    {
        $prefix = $this->getOptionsKeyPrefixForNamespace();
        if ($this->options[$prefix.'installed'] ?? false || static::Setting($prefix.'installed') ?? false) {
            return true;
        }
        return $this->getInstaller()->isInstalled();
    }
    ////
    public function checkInstall()
    {
        return $this->getInstaller()->checkInstall();
    }
    public function install($parameters)
    {
        return $this->getInstaller()->install($parameters);
    }
    protected function getInstaller()
    {
        $prefix = $this->getOptionsKeyPrefixForNamespace();
        $table_prefix = $this->options[$prefix. 'table_prefix'] ?? '';
        
        $options = [
            'install_table_prefix' => $table_prefix,
        ];
        return Installer::G()->init($options, $this);
    }
}
