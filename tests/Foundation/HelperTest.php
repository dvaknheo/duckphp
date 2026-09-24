<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\DuckPhp;
use DuckPhp\DuckPhpAllInOne;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\Business\BusinessHelper;
use DuckPhp\Foundation\Controller\ControllerHelper;
use DuckPhp\Foundation\Model\ModelHelper;
use DuckPhp\Foundation\System\SystemHelper;
use DuckPhp\GlobalAdmin\Admin;
use DuckPhp\GlobalUser\User;
use PHPUnit\Framework\Assert;

/**
 * 四层并集（Foundation\Helper / DuckPhpAllInOne）的一致性与防漂移测试。
 *
 * 设计（作者裁定，与 master 一致）：并集**不声明**任何 Helper 方法，而是用
 * `__callStatic` 按固定顺序在四个层 Helper 里找第一个 `method_exists` 的实现并转发。
 * 本文件负责保证：
 *   1) 96 个方法名都能被派发到真实实现（逐名调用冒烟）；
 *   2) 派发顺序被钉住（System → Controller → Business → Model），并据此算出 12 个
 *      跨层重名方法的胜出方（其中 6 个与历史 insteadof 的裁定不同，见测试注释）；
 *   3) 未定义方法走 trigger_error(E_USER_ERROR) 报错，不会被静默吞掉；
 *   4) 四个层 Helper 才是方法/事件属性的真正持有者（并集类不再自带事件属性）；
 *   5) ThrowOn 这个唯一「语义可辨」的重名项确实取 System（Project）版本。
 */
class HelperTest extends \PHPUnit\Framework\TestCase
{
    /** 层 => 该层的 Helper 类 */
    const LAYER_CLASS = [
        'Model' => ModelHelper::class,
        'Business' => BusinessHelper::class,
        'Controller' => ControllerHelper::class,
        'System' => SystemHelper::class,
    ];

    /** __callStatic 的查找顺序（改这里等于改重名方法的胜出方） */
    const DISPATCH_ORDER = ['System', 'Controller', 'Business', 'Model'];

    /**
     * 跨层重名方法 => 按上面顺序的胜出层（= DISPATCH_ORDER 里第一个声明它的层）。
     *
     * 与旧 `insteadof` 裁定的差异（8 个名字换了主人，但**行为等价**）：
     *   - `Setting`/`AppOptions`/`Config`/`XpCall`：旧裁定 Business，魔术顺序下 Controller
     *     （两版都是转发到 App/Configer/CoreHelper，等价）；
     *   - `header`/`setcookie`/`exit`：旧裁定 Controller，魔术顺序下 **System**（System 也声明了
     *     这三个，且实现逐字相同：`SystemWrapper::_()->_header/_setcookie/_exit`）；
     *   - `FireGlobalEvent`/`OnGlobalEvent`：旧裁定 Business，魔术顺序下 **System**（三层实现相同）。
     * 唯一语义可辨的 `ThrowOn` 两种裁定都是 System（Project 版）；`AdminService`/`UserService`
     * 两种裁定都是 Controller。
     */
    const CONFLICT_WINNER = [
        'Setting' => 'Controller',
        'AppOptions' => 'Controller',
        'Config' => 'Controller',
        'XpCall' => 'Controller',
        'FireGlobalEvent' => 'System',
        'OnGlobalEvent' => 'System',
        'ThrowOn' => 'System',
        'header' => 'System',
        'setcookie' => 'System',
        'exit' => 'System',
        'AdminService' => 'Controller',
        'UserService' => 'Controller',
    ];

    /**
     * 事件常量 => 该常量唯一合法的宿主层。
     *
     * 源码现状（提交 3ed3b053 起）：顶层 `GlobalUser\User` / `GlobalAdmin\Admin` 各持 6+4 个
     * 常量（`EVENT_ACTION_*` / `EVENT_SERVICE_*`），层 Helper 里只留**别名声明**
     * （`BusinessHelper` 收 SERVICE_*、`ControllerHelper` 收 ACTION_*），旧的那批
     * `public static $EVENT_REGISTERING = 'registering'` 属性已从 `src/` 删除。
     */
    const EVENT_CONSTS = [
        'Business' => [
            'EVENT_SERVICE_USER_REGISTERING', 'EVENT_SERVICE_USER_REGISTERED',
            'EVENT_SERVICE_USER_LOGINING', 'EVENT_SERVICE_USER_LOGINED',
            'EVENT_SERVICE_USER_LOGOUTING', 'EVENT_SERVICE_USER_LOGOUTED',
            'EVENT_SERVICE_ADMIN_LOGINING', 'EVENT_SERVICE_ADMIN_LOGINED',
            'EVENT_SERVICE_ADMIN_LOGOUTING', 'EVENT_SERVICE_ADMIN_LOGOUTED',
        ],
        'Controller' => [
            'EVENT_ACTION_USER_REGISTERING', 'EVENT_ACTION_USER_REGISTERED',
            'EVENT_ACTION_USER_LOGINING', 'EVENT_ACTION_USER_LOGINED',
            'EVENT_ACTION_USER_LOGOUTING', 'EVENT_ACTION_USER_LOGOUTED',
            'EVENT_ACTION_ADMIN_LOGINING', 'EVENT_ACTION_ADMIN_LOGINED',
            'EVENT_ACTION_ADMIN_LOGOUTING', 'EVENT_ACTION_ADMIN_LOGOUTED',
        ],
    ];

    const EXPECTED_UNION_SIZE = 96;

    protected function setUp(): void
    {
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        DuckPhp::_()->init([
            'path' => $path_app,
            'is_debug' => true,
            'namespace' => __NAMESPACE__,
        ]);
    }

    /** 类自己声明的 public static 方法（不含继承、不含 SonstletonExTrait 的 _()） */
    protected function staticApi(string $class): array
    {
        $rc = new \ReflectionClass($class);
        $ret = [];
        foreach ($rc->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if (!$m->isStatic()) {
                continue;
            }
            if ($m->getDeclaringClass()->getName() !== $rc->getName()) {
                continue;
            }
            $ret[$m->getName()] = $m;
        }
        unset($ret['_'], $ret['__callStatic']);
        return $ret;
    }

    /** 名字 => 按 DISPATCH_ORDER 首次命中的层 */
    protected function dispatchedTargets(): array
    {
        $ret = [];
        foreach (self::DISPATCH_ORDER as $layer) {
            foreach (array_keys($this->staticApi(self::LAYER_CLASS[$layer])) as $name) {
                if (!isset($ret[$name])) {
                    $ret[$name] = $layer;
                }
            }
        }
        return $ret;
    }

    // ---------------------------------------------------------------- 1) 四层仍是真宿主
    public function testLayersHoldTheWholeApi()
    {
        $targets = $this->dispatchedTargets();
        Assert::assertCount(self::EXPECTED_UNION_SIZE, $targets, '四层并集方法数变了，请同步重名方法的裁定');

        // 并集类自己不声明任何 Helper 方法（只有 _() 与 __callStatic 的语义留给魔术方法）
        foreach ([Helper::class, DuckPhpAllInOne::class] as $class) {
            Assert::assertSame([], array_keys($this->staticApi($class)), "$class 不应再显式声明 Helper 方法");
        }

        // 事件常量只住在层 Helper 上（Business 10 个 SERVICE_* + Controller 10 个 ACTION_*），
        // 且每个常量的值必须与顶层 User/Admin 的同名常量一致（层里写的是别名声明）。
        foreach (self::EVENT_CONSTS as $layer => $names) {
            foreach ($names as $name) {
                $owners = [];
                foreach (self::LAYER_CLASS as $l => $class) {
                    if ((new \ReflectionClass($class))->hasConstant($name)) {
                        $owners[] = $l;
                    }
                }
                Assert::assertSame([$layer], $owners, "事件常量 $name 应恰好由 $layer 层持有");
                $top = strpos($name, '_ADMIN_') !== false ? Admin::class : User::class;
                Assert::assertSame(
                    constant($top.'::'.$name),
                    constant(self::LAYER_CLASS[$layer].'::'.$name),
                    "$name 的值应与 $top 的同名常量一致"
                );
            }
        }
    }

    // ---------------------------------------------------------------- 2) 派发顺序被钉住
    public function testDispatchOrderAndWinnersArePinned()
    {
        // 用位置排序核对派发顺序（不用正则，免得转义打架）：源码里出现得越早，越先被命中
        $orders = [];
        foreach (['src/Foundation/Helper.php', 'src/DuckPhpAllInOne.php'] as $rel) {
            $src = file_get_contents(dirname(__DIR__, 2) . '/' . $rel);
            $found = [];
            foreach (self::DISPATCH_ORDER as $layer) {
                $needle = '\\DuckPhp\\Foundation\\' . $layer . '\\' . $layer . 'Helper::class';
                $pos = strpos($src, $needle);
                Assert::assertNotFalse($pos, "$rel 里找不到 {$layer}Helper 的派发条目");
                $found[$pos] = $layer;
            }
            ksort($found);
            $orders[$rel] = array_values($found);
        }
        Assert::assertSame(self::DISPATCH_ORDER, $orders['src/Foundation/Helper.php'], 'Foundation\Helper 的派发顺序变了');
        Assert::assertSame(
            self::DISPATCH_ORDER,
            $orders['src/DuckPhpAllInOne.php'],
            'DuckPhpAllInOne 的派发顺序与 Foundation\Helper 不一致'
        );
        // 重名方法：按顺序算出的胜出方必须与钉住的表一致
        $layers_of = [];
        foreach (self::LAYER_CLASS as $layer => $class) {
            foreach (array_keys($this->staticApi($class)) as $name) {
                $layers_of[$name][] = $layer;
            }
        }
        $conflicts = [];
        foreach ($layers_of as $name => $layers) {
            if (count($layers) > 1) {
                $conflicts[$name] = $this->dispatchedTargets()[$name];
            }
        }
        ksort($conflicts);
        $expected = self::CONFLICT_WINNER;
        ksort($expected);
        Assert::assertSame($expected, $conflicts, '跨层重名方法的胜出方变了');
    }

    // ---------------------------------------------------------------- 3) 未定义方法
    public function testUnknownMethodRaisesUserError()
    {
        foreach ([Helper::class, DuckPhpAllInOne::class] as $class) {
            $errorTriggered = false;
            set_error_handler(function ($errno, $errstr) use (&$errorTriggered) {
                if (strpos($errstr, 'Call to undefined method') !== false) {
                    $errorTriggered = true;
                }
                return true;
            });
            $class::nonExistentMethod();
            restore_error_handler();
            Assert::assertTrue($errorTriggered, "$class 调用不存在的静态方法应触发错误");
        }
    }

    // ---------------------------------------------------------------- 4) 逐名派发冒烟
    public function testSmokeEveryDispatchedMethod()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);

        Helper::system_wrapper_replace([
            'header' => function () {},
            'setcookie' => function () {},
            'exit' => function ($code = 0) {},
        ]);

        foreach (array_keys($this->dispatchedTargets()) as $name) {
            $this->callWithDummyArgs(Helper::class, $name);
            $this->callWithDummyArgs(DuckPhpAllInOne::class, $name);
        }
        $errorTriggered = false;
        set_error_handler(function ($errno, $errstr) use (&$errorTriggered) {
            if (strpos($errstr, 'Call to undefined method') !== false) {
                $errorTriggered = true;
            }
            return true;
        });
        Helper::nonExistentMethod();
        restore_error_handler();
        Assert::assertTrue($errorTriggered, 'Should trigger error for non-existent method');

        \LibCoverage\LibCoverage::End();
    }

    protected function callWithDummyArgs(string $class, string $name): void
    {
        // 并集类没有声明这些方法，参数只能用「层里同名方法」的签名来生成
        $target = $this->dispatchedTargets()[$name];
        $m = new \ReflectionMethod(self::LAYER_CLASS[$target], $name);
        $args = [];
        foreach ($m->getParameters() as $p) {
            $args[] = $this->dummyValue($p);
        }
        try {
            $class::$name(...$args);
        } catch (\Throwable $ex) {
            // 冒烟只保证派发链路能走通，不保证方法在空环境下能成功
        }
    }

    protected function dummyValue(\ReflectionParameter $p)
    {
        $t = $p->getType();
        $n = $t === null ? '' : $t->getName();
        switch ($n) {
            case 'string':
                return '';
            case 'int':
                return 0;
            case 'bool':
                return false;
            case 'array':
                return [];
            case 'callable':
                return function () {};
            case 'Throwable':
                return new \Exception('smoke');
            case 'SessionHandlerInterface':
                return new HelperUnionFakeSessionHandler();
        }
        return $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null;
    }

    // ---------------------------------------------------------------- 5) 冲突语义
    public function testThrowOnConflictTakesProjectVersion()
    {
        $app = \DuckPhp\Core\App::_();
        $app->options['exception_for_project'] = HelperUnionProjectException::class;
        $app->options['exception_for_business'] = HelperUnionBusinessException::class;
        $app->options['exception_for_controller'] = HelperUnionControllerException::class;

        $cases = [
            [Helper::class, HelperUnionProjectException::class, '并集按顺序取到 System（Project）版'],
            [SystemHelper::class, HelperUnionProjectException::class, 'System 层即 Project 版'],
            [BusinessHelper::class, HelperUnionBusinessException::class, 'Business 层取 Business 版'],
            [ControllerHelper::class, HelperUnionControllerException::class, 'Controller 层取 Controller 版'],
            [DuckPhpAllInOne::class, HelperUnionProjectException::class, 'AllInOne 与并集同规则'],
        ];
        foreach ($cases as $one) {
            list($class, $expected, $why) = $one;
            try {
                $class::ThrowOn(true, 'boom');
                Assert::fail("$class::ThrowOn 应该抛异常（{$why}）");
            } catch (HelperUnionProjectException | HelperUnionBusinessException | HelperUnionControllerException $ex) {
                Assert::assertInstanceOf($expected, $ex, "$class::ThrowOn 选错了层（{$why}）");
            }
        }
    }
}

class HelperUnionFakeSessionHandler implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }
    public function read($id): string
    {
        return '';
    }
    public function write($id, $data): bool
    {
        return true;
    }
    public function destroy($id): bool
    {
        return true;
    }
    public function gc($maxlifetime): int
    {
        return 0;
    }
}
class HelperUnionProjectException extends \Exception
{
}
class HelperUnionBusinessException extends \Exception
{
}
class HelperUnionControllerException extends \Exception
{
}
