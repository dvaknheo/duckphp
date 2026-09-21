<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\DuckPhp;
use DuckPhp\DuckPhpAllInOne;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\Business\Helper as BusinessHelper;
use DuckPhp\Foundation\Controller\Helper as ControllerHelper;
use DuckPhp\Foundation\Model\Helper as ModelHelper;
use DuckPhp\Foundation\System\Helper as SystemHelper;
use PHPUnit\Framework\Assert;

/**
 * 四层并集门面的一致性与防漂移测试。
 *
 * Foundation\Helper 与 DuckPhpAllInOne 不再用 trait 组合，而是「一行转发」到四层 Helper；
 * 本文件负责保证：
 *   1) 转发目标与历史 insteadof 冲突消解表一致（逐方法核对源码里的转发目标）；
 *   2) 方法集 / 签名与四层 Helper 完全一致（少一个方法、丢一个类型注解都会红）；
 *   3) 事件属性（原来由 trait 白拿）仍然存在且值一致；
 *   4) 96 个方法都能被真实调用（哑参数冒烟）；
 *   5) ThrowOn 这个唯一「语义可辨」的冲突项确实取 System（Project）版本。
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

    /** 12 个跨层重名方法 => 胜出层（与旧 insteadof 块逐字一致） */
    const CONFLICT = [
        'Setting' => 'Business',
        'AppOptions' => 'Business',
        'Config' => 'Business',
        'XpCall' => 'Business',
        'FireGlobalEvent' => 'Business',
        'OnGlobalEvent' => 'Business',
        'ThrowOn' => 'System',
        'header' => 'Controller',
        'setcookie' => 'Controller',
        'exit' => 'Controller',
        'AdminService' => 'Controller',
        'UserService' => 'Controller',
    ];

    const EVENT_PROPS = [
        'EVENT_REGISTERING', 'EVENT_REGISTERED', 'EVENT_LOGINING', 'EVENT_LOGINED',
        'EVENT_ACTION_REGISTERING', 'EVENT_ACTION_REGISTERED', 'EVENT_ACTION_LOGINING',
        'EVENT_ACTION_LOGINED', 'EVENT_ACTION_LOGOUTING', 'EVENT_ACTION_LOGOUTED',
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

    /** 类自己声明的 public static 方法（不含继承、不含 SingletonExTrait 的 _()） */
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
        unset($ret['_']);
        return $ret;
    }

    /** 名字 => 应该转发到的层 */
    protected function expectedTargets(): array
    {
        $layers_of = [];
        foreach (self::LAYER_CLASS as $layer => $class) {
            foreach (array_keys($this->staticApi($class)) as $name) {
                $layers_of[$name][] = $layer;
            }
        }
        $ret = [];
        foreach ($layers_of as $name => $layers) {
            $ret[$name] = count($layers) === 1 ? $layers[0] : self::CONFLICT[$name];
        }
        return $ret;
    }

    protected function typeName(?\ReflectionType $t): string
    {
        if ($t === null) {
            return '';
        }
        $n = $t->getName();
        $s = $t->isBuiltin() ? $n : '\\' . $n;
        if ($t->allowsNull() && $n !== 'null' && $n !== 'mixed') {
            $s = '?' . $s;
        }
        return $s;
    }

    protected function signatureOf(\ReflectionMethod $m): array
    {
        $ret = ['params' => [], 'return' => $this->typeName($m->getReturnType())];
        foreach ($m->getParameters() as $p) {
            $ret['params'][] = [
                'name' => $p->getName(),
                'type' => $this->typeName($p->getType()),
                'by_ref' => $p->isPassedByReference(),
                'variadic' => $p->isVariadic(),
                'has_default' => $p->isDefaultValueAvailable(),
                'default' => $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null,
            ];
        }
        return $ret;
    }

    // ---------------------------------------------------------------- 1) 转发目标
    public function testForwardTargetsMatchConflictTable()
    {
        $expected = $this->expectedTargets();
        Assert::assertCount(self::EXPECTED_UNION_SIZE, $expected, '四层并集方法数变了，请同步 12 个冲突项的裁定');

        $src = file_get_contents(dirname(__DIR__, 2) . '/src/Foundation/Helper.php');
        preg_match_all('/\\\\DuckPhp\\\\Foundation\\\\(\w+)\\\\Helper::(\w+)\(/', $src, $m, PREG_SET_ORDER);
        $actual = [];
        foreach ($m as $one) {
            $actual[$one[2]] = $one[1];
        }

        ksort($expected);
        ksort($actual);
        foreach ($expected as $name => $layer) {
            Assert::assertArrayHasKey($name, $actual, "Foundation\\Helper 缺少转发：$name");
            Assert::assertSame($layer, $actual[$name], "Foundation\\Helper::$name 转发到了 {$actual[$name]}，应为 $layer");
        }
        Assert::assertSame(array_keys($expected), array_keys($actual), 'Foundation\\Helper 里有多余的转发');
    }

    // ---------------------------------------------------------------- 2) 方法集与签名
    public function testSurfaceAndSignaturesMatchLayers()
    {
        $union = $this->expectedTargets();
        $actual = $this->staticApi(Helper::class);

        $missing = array_diff(array_keys($union), array_keys($actual));
        $extra = array_diff(array_keys($actual), array_keys($union));
        Assert::assertSame([], array_values($missing), 'Foundation\\Helper 少了方法');
        Assert::assertSame([], array_values($extra), 'Foundation\\Helper 多了方法');

        foreach ($union as $name => $layer) {
            $expected_sig = $this->signatureOf(new \ReflectionMethod(self::LAYER_CLASS[$layer], $name));
            Assert::assertSame($expected_sig, $this->signatureOf($actual[$name]), "Foundation\\Helper::$name 签名与 $layer 层不一致");
        }
    }

    public function testAllInOneCarriesWholeUnion()
    {
        $union = $this->expectedTargets();
        $actual = $this->staticApi(DuckPhpAllInOne::class);

        $missing = array_diff(array_keys($union), array_keys($actual));
        Assert::assertSame([], array_values($missing), 'DuckPhpAllInOne 少了 Helper 方法');

        foreach ($union as $name => $layer) {
            $expected_sig = $this->signatureOf(new \ReflectionMethod(self::LAYER_CLASS[$layer], $name));
            Assert::assertSame($expected_sig, $this->signatureOf($actual[$name]), "DuckPhpAllInOne::$name 签名与 $layer 层不一致");
        }
    }

    // ---------------------------------------------------------------- 3) 事件属性
    public function testEventPropertiesSurviveOnBothUnions()
    {
        foreach (self::EVENT_PROPS as $name) {
            $owner = null;
            foreach (['Business', 'Controller'] as $layer) {
                $rc = new \ReflectionClass(self::LAYER_CLASS[$layer]);
                if ($rc->hasProperty($name)) {
                    $owner = $rc;
                }
            }
            Assert::assertNotNull($owner, "四层 Helper 里找不到事件属性 $name");

            foreach ([Helper::class, DuckPhpAllInOne::class] as $class) {
                $rc = new \ReflectionClass($class);
                Assert::assertTrue($rc->hasProperty($name), "$class 丢了事件属性 $name");
                Assert::assertSame(
                    $owner->getStaticPropertyValue($name),
                    $rc->getStaticPropertyValue($name),
                    "$class::$name 的值与层 Helper 不一致"
                );
            }
        }
    }

    // ---------------------------------------------------------------- 4) 冒烟
    public function testSmokeEveryForwarder()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);

        Helper::system_wrapper_replace([
            'header' => function () {},
            'setcookie' => function () {},
            'exit' => function ($code = 0) {},
        ]);

        foreach (array_keys($this->expectedTargets()) as $name) {
            $this->callWithDummyArgs(Helper::class, $name);
            $this->callWithDummyArgs(DuckPhpAllInOne::class, $name);
        }

        \LibCoverage\LibCoverage::End();
    }

    protected function callWithDummyArgs(string $class, string $name): void
    {
        $m = new \ReflectionMethod($class, $name);
        $args = [];
        foreach ($m->getParameters() as $p) {
            $args[] = $this->dummyValue($p);
        }
        try {
            $m->invoke(null, ...$args);
        } catch (\Throwable $ex) {
            // 冒烟只保证转发链路能走通，不保证方法在空环境下能成功
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
            [Helper::class, HelperUnionProjectException::class, '并集取 System（Project）版'],
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

    // ---------------------------------------------------------------- 6) 未定义方法
    public function testUnknownMethodRaisesError()
    {
        // 并集是显式方法集：不存在的静态方法由 PHP 直接报 Error（旧版 __callStatic 魔术已移除）
        foreach ([Helper::class, DuckPhpAllInOne::class] as $class) {
            try {
                $class::nonExistentMethod();
                Assert::fail("$class 调用不存在的静态方法应该报错");
            } catch (\Error $ex) {
                Assert::assertStringContainsString('nonExistentMethod', $ex->getMessage());
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
