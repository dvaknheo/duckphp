<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace tests_Data_Installer\System;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\Installer;

class InstallerApp extends DuckPhp
{
    protected function onBeforeRun()
    {
        $this->checkInstall();
    }
    ////[[[[
            // 表格前缀， session 前缀
    public function getTablePrefix()
    {
        return 'test_';//$this->options['duckadmin_table_prefix'];
    }
    public function isInstalled()
    {
        //if ($this->options['duckadmin_installed'] || static::Setting('duckadmin_installed') ){
        //    return true;
        //}
        return $this->getInstaller([])->isInstalled();
    }
    public function checkInstall()
    {
        return $this->getInstaller([])->checkInstall();
    }
    public function install($parameters)
    {
        echo $this->getInstaller([])->install($parameters);
    }
    protected function getInstaller($options=[])
    {
        $ex_options = [
            'install_table_prefix' => $this->getTablePrefix(),
        ];
        $options = array_merge($options, $ex_options);
        $class = get_class(Installer::G());
        Installer::G(new $class);
        return Installer::G()->init($options,$this);
    }
    ////]]]]
}