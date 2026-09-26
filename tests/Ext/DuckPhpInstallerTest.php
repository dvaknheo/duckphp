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

        // `show` 服务的目录必须真的存在：它曾指向早已改名成 `demo/` 的 `template/`，
        // 于是命令只打印 `Directory ... does not exist.`，靠上面那个 stub HttpServer 是查不出来的。
        $rm_demo = new \ReflectionMethod(\DuckPhp\Ext\DuckPhpInstaller::class, 'getDemoPath');
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $rm_demo->setAccessible(true);
        }
        $demo_path = (string) $rm_demo->invoke(DuckPhpInstaller::_());
        $this->assertDirectoryExists($demo_path, 'show 命令服务的 demo 目录应当存在');
        $this->assertFileExists($demo_path . '/public/index.php');
        
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
        // 从**生成后的目录**推类名，而不是写死一张名单：名单会随骨架改名/挪目录而过期
        // （`ExceptionReporter` 改名并挪到 `Controller\ExceptionAction` 之后就漏过一次，
        // 直到下一次全量跑才暴露）。骨架里文件 basename 就是类名，子目录就是子命名空间；
        // `App.php` 已被安装器改名为 `NSXApp.php`，所以推导规则对它同样成立。
        $skeleton_classes = [];
        foreach (glob($path_nsx . '/src/*/*.php') ?: [] as $file) {
            $skeleton_classes[] = 'NSX\\' . basename(dirname($file)) . '\\' . basename($file, '.php');
        }
        sort($skeleton_classes);
        $this->assertNotEmpty($skeleton_classes, '生成目录里应当有骨架类');
        $not_loaded = [];
        foreach ($skeleton_classes as $class) {
            if (!class_exists($class)) {
                $not_loaded[] = $class;
            }
        }
        $this->assertSame([], $not_loaded, '骨架类应能全部加载（签名不兼容会在这里暴露）');

        // 入口与随附文档里也不能留下**旧命名空间**或**没改名的 App 类**：安装器把
        // `src/System/App.php` 改名为 `{NS}App.php` 之后，凡是提到这个类的地方都要跟着改——
        // 曾只改了 public/index.php，于是新工程跑 `php bin/cli.php help` 直接
        // `Class "…\System\App" not found`；`AGENTS.md` 的目录树同样要跟着改。
        $entry_files = array_merge(
            glob($path_nsx . '/src/*/*.php') ?: [],
            [
                $path_nsx . '/bin/cli.php',
                $path_nsx . '/public/index.php',
                $path_nsx . '/AGENTS.md',
            ]
        );
        $stale = [];
        foreach ($entry_files as $file) {
            $data = (string) file_get_contents($file);
            if (strpos($data, 'YourProjectName') !== false || strpos($data, 'System\\App::') !== false) {
                $stale[] = substr($file, strlen($path_nsx) + 1);
            }
        }
        $this->assertSame([], $stale, '生成的文件里不该再留下旧命名空间或未改名的 App 类引用');

        // 生成的 AGENTS.md 里那条 `src/System/{NS}App.php` 必须真的存在（旧写法是 `App.php`），
        // 而且工程根目录不该有 `cli.php` 这种陈旧路径（入口在 bin/cli.php）。
        $agents = (string) file_get_contents($path_nsx . '/AGENTS.md');
        $this->assertStringContainsString('src/System/NSXApp.php', $agents);
        $this->assertFileExists($path_nsx . '/src/System/NSXApp.php');
        $this->assertFileExists($path_nsx . '/AGENTS.md');
        $this->assertFileExists($path_nsx . '/CLAUDE.md');
        $this->assertFileDoesNotExist($path_nsx . '/cli.php');
        $this->assertFileDoesNotExist($path_nsx . '/RULES.md');

        // 端到端冒烟：先补一个 composer 本会给它的 autoload（单测里不跑 composer），
        // 再按用户的方式跑生成工程自己的 CLI 入口。
        @mkdir($path_nsx . '/vendor');
        file_put_contents($path_nsx . '/vendor/autoload.php', str_replace(
            ['@AUTOLOAD@', '@SRCDIR@'],
            [var_export(realpath(__DIR__ . '/../../vendor/autoload.php'), true), var_export($path_nsx . '/src/', true)],
            <<<'EOT'
<?php
require @AUTOLOAD@;
spl_autoload_register(function ($class) {
    if (strpos($class, 'NSX\\') !== 0) {
        return;
    }
    $file = @SRCDIR@ . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
EOT
        ));
        $cli_out = (string) shell_exec('php ' . escapeshellarg($path_nsx . '/bin/cli.php') . ' help 2>&1');
        $this->assertStringContainsString('DuckPhp', $cli_out, '生成的 bin/cli.php 应当能跑起来并打印 CLI 帮助');
        $this->assertStringNotContainsString('not found', $cli_out);
        $this->assertStringNotContainsString('Fatal error', $cli_out);

        // 骨架自带的示例路由 `/test/done` 必须连同视图一起生成（`testController::done()` 不给视图名，
        // 框架按路由找 view/test/done.php；少了这个文件该路由就是 500）。
        $this->assertFileExists($path_nsx . '/view/test/done.php');
        $this->assertFileExists($path_nsx . '/view/main.php');

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