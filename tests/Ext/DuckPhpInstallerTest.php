<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\DuckPhpInstaller;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;
use DuckPhp\HttpServer\HttpServer;
class InstallerConsole extends Console
{

    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        if(empty($this->data)){
            $fp_in = fopen('php://temp','r');
        }
        $fp_out = fopen('php://temp','w');
        $data = parent::readLines($options, $desc, [],$fp_in,$fp_out);
        fclose($fp_out);
        if(empty($this->data)){
            fclose($fp_in);
        }
        return $data;
    }
}
class DuckPhpInstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpInstaller::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhpInstaller::class);
        $path_init = $path;
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        $__SERVER = $_SERVER;
        $_SERVER['argv']=[];
        
        $time = date('Y-m-d_H_i_s');
        $path = $path . $time . 'test';
        mkdir($path);
        Console::_(InstallerConsole::_());
        $options=[
            'is_debug'=>true,
            'path'=>$path,
            'verbose'=>true,
        ];
        $_SERVER['argv']=[];
        DuckPhpInstaller::_()->command_help();
        
        $_SERVER['argv']=[
            '-','run', '--http-server=tests/DuckPhp/Ext/Console_HttpServer',
        ];
        DuckPhpInstaller::_()->command_show();
        
        $_SERVER['argv']=[
            '-','new','--help',
        ];
        DuckPhpInstaller::_()->command_new();
        
        $_SERVER['argv']=[
            '-','new','--verbose','--path='.$path,
        ];
        $str= "Abcde\n";
        Console::_()->readLinesCleanFill();
        Console::_()->readLinesFill($str);
        DuckPhpInstaller::_()->command_new();
        Console::_()->readLinesCleanFill();
        Console::_()->readLinesFill($str);
        DuckPhpInstaller::_()->command_new();
        Console::_()->readLinesCleanFill();
        
        $ns = 'NSX';
        $json = [
            'autoload' =>[
                'psr-4' =>[
                    $ns => 'src',
                ],
            ]
        ];
        file_put_contents($path.'/composer.json', json_encode($json));
        
        DuckPhpInstaller::_()->command_new();

        // fresh path with NSX namespace: src/System/App.php renamed to src/System/NSXApp.php
        $path_nsx = $path.'_nsx';
        mkdir($path_nsx);
        file_put_contents($path_nsx.'/composer.json', json_encode($json));
        $_SERVER['argv'] = ['-', 'new', '--verbose', '--path='.$path_nsx];
        DuckPhpInstaller::_()->command_new();
        $this->assertFileExists($path_nsx.'/src/System/NSXApp.php');
        $this->assertFileDoesNotExist($path_nsx.'/src/System/App.php');
        $app_data = (string) file_get_contents($path_nsx.'/src/System/NSXApp.php');
        $this->assertStringContainsString('class NSXApp extends DuckPhp', $app_data);

        // 生成的骨架必须**真的能加载**：方法签名与父类不兼容（例如 onInited() 少了 `: void`）
        // 只在类被编译时才炸，光看文件内容是查不出来的。
        spl_autoload_register(function ($class) use ($path_nsx) {
            if (strpos($class, 'NSX\\') !== 0) {
                return;
            }
            $file = $path_nsx . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (is_file($file)) {
                require $file;
            }
        });
        $skeleton_classes = [
            'NSX\\System\\NSXApp',
            'NSX\\System\\ProjectException',
            'NSX\\System\\BusinessException',
            'NSX\\System\\ControllerException',
            'NSX\\System\\ExceptionReporter',
            'NSX\\Controller\\Helper',
            'NSX\\Controller\\Base',
            'NSX\\Controller\\AppAction',
            'NSX\\Controller\\MainController',
            'NSX\\Controller\\Session',
            'NSX\\Business\\Helper',
            'NSX\\Business\\Base',
            'NSX\\Business\\DemoBusiness',
            'NSX\\Model\\Base',
            'NSX\\Model\\DemoModel',
        ];
        $not_loaded = [];
        foreach ($skeleton_classes as $class) {
            if (!class_exists($class)) {
                $not_loaded[] = $class;
            }
        }
        $this->assertSame([], $not_loaded, '骨架类应能全部加载（签名不兼容会在这里暴露）');
        // Model\Base 的 6 个数据层助手是显式声明，实例式调用应可用
        $model = new class extends \NSX\Model\Base {
        };
        $this->assertTrue(is_callable([$model, 'Db']));
        $this->assertTrue(is_callable([$model, 'DatabaseDriver']));
        
        // getNamespaceBasename: 空 namespace 分支
        $rm = new \ReflectionMethod(\DuckPhp\Ext\DuckPhpInstaller::class, 'getNamespaceBasename');
        
        if (version_compare(PHP_VERSION,'8.1.0','<')) {
            $rm->setAccessible(true);
        }
        $installer = DuckPhpInstaller::_();
        $installer->options['namespace'] = '';
        $this->assertSame('', $rm->invoke($installer));
        $installer->options['namespace'] = 'NSX';
        $this->assertSame('NSX', $rm->invoke($installer));

        // newProject() 不传 path 时：用 $_SERVER['SCRIPT_FILENAME'] 往上两级推断项目目录（line 99）。
        // 把 SCRIPT_FILENAME 指到本测试自己的临时目录，既覆盖这一行，又不至于真往仓库根写文件。
        $path_auto = $path . '_auto';
        mkdir($path_auto . '/deep/tmp', 0777, true);
        file_put_contents(
            $path_auto . '/composer.json',
            json_encode(['autoload' => ['psr-4' => ['AutoNS' => 'src']]])
        );
        $script_filename = $_SERVER['SCRIPT_FILENAME'];
        $_SERVER['SCRIPT_FILENAME'] = $path_auto . '/deep/tmp/index.php';
        $_SERVER['argv'] = ['-', 'new', '--verbose'];
        DuckPhpInstaller::_()->command_new();
        $_SERVER['SCRIPT_FILENAME'] = $script_filename;
        // 骨架落在 SCRIPT_FILENAME 上两级那个目录，命名空间取自那里的 composer.json
        $this->assertFileExists($path_auto . '/src/System/AutoNSApp.php');
        $this->assertStringContainsString(
            'class AutoNSApp extends DuckPhp',
            (string) file_get_contents($path_auto . '/src/System/AutoNSApp.php')
        );

        $_SERVER = $__SERVER;
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        \LibCoverage\LibCoverage::End();
    }

}
class Console_HttpServer extends HttpServer
{
    public function run()
    {
        return true;
    }
}