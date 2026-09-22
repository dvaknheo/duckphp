# Helper Trait 合并进 Foundation · 进度 Checklist

> 分支：`260927-helper`（提交由作者执行，本文件只记录进度与验收数字）
>
> 配套阅读：[用户指南维护指南](guide-maintenance-guide.md)（写作/校验规则）、[参考手册维护指南](reference-maintenance-guide.md)（参考页模板与工具）。
> 本文件只管**做到哪了**与**验收证据**。
>
> **勾选纪律**：一项只在「改完 + 实跑过 + 校验命令通过」之后才勾；只改措辞/格式不算完成。

## 目标（作者裁定）

- **方案 A（字面合并）**：`src/Helper/` 的 4 个 trait 的方法体**直接写进**对应层的 `Foundation\<层>\Helper` **类**，`src/Helper/` 目录删除。
- **并集用显式转发**：`Foundation\Helper` 与 `DuckPhpAllInOne` 这两个「四层并集」持有者改为显式静态转发（各 96 个一行方法），并加反射一致性测试兜底同步。
- **不留兼容层**：`DuckPhp\Helper\*` 直接消失，不做薄壳/别名；文档同步改干净。
- **不再有任何 trait**（不保留「转发 trait」的折中形态）。
- 文件移动走 `git mv`（保留改名历史）；**提交由作者执行**。

## 事实基线（开工前核查）

| 项 | 事实 |
|---|---|
| 待合并 trait | `AppHelperTrait` 40 / `BusinessHelperTrait` 17 / `ControllerHelperTrait` 48 / `ModelHelperTrait` 6 个静态方法；**并集 96 个，重名 12 个** |
| 引用它们的 php | 19 个（src 6 / demo 3 / skeleton 3 / `tests/Helper` 4 / `ZThirdDemo` 3） |
| 并集持有者 | `src/Foundation/Helper.php`、`src/DuckPhpAllInOne.php`（各 4 个 `use` + 12 条 `insteadof`） |
| 事件属性 | Business 4 个 + Controller 6 个；**只由这两层 Helper 持有**（并集类不再自带，核对过全仓无引用） |
| trait 方法体自引用 | `self::` / `static::` / `$this->` **0 命中** ⇒ 换宿主行为等价 |
| `_()` | 并集类需自己 `use SingletonExTrait`；`DuckPhpAllInOne::_()` 由父类 `App`（经 `ComponentBase`）提供，语义相同 |
| 连带影响 | `ZAllDemoTest` 的 `files` 路由输出含 src 文件路径与调用栈行号 ⇒ 期望值必变 |

## 冲突消解表（照抄现有 insteadof，不变）

| 重名方法 | 胜出 → 转发到 |
|---|---|
| `Setting` `AppOptions` `Config` `XpCall` | Business |
| `FireGlobalEvent` `OnGlobalEvent` | Business |
| `ThrowOn` | System（`_ProjectThrowOn` 语义） |
| `header` `setcookie` `exit` | Controller |
| `AdminService` `UserService` | Controller |

⇒ 并集 96 个来源（生成器实测）：**System 35 + Business 14 + Controller 41 + Model 6**。

---

## 分支同步：rebase 到 master —— ✅ 已完成

**为什么是 rebase 而不是 merge**：`doced`（52221766）是 `master` 的祖先，master 只是多了 11 个提交；本分支在 doced 之上只有 1 个提交，且**从未 push**（无 upstream）。rebase 后历史是线性的 `master → Helper Trait 合并`，需要解的冲突与 merge 完全相同（都是 4 个文件），所以取 rebase。

```
* 02209c67 (HEAD -> 260927-helper) Helper Trait 合并
* 222b71b0 (master) 格式化
```

**冲突（4 个）**：master 自己已经用**另一套方案**实现了同一个目标 —— `Foundation\Helper` / `DuckPhpAllInOne` 改成 `__callStatic` 魔术转发（按 `System → Controller → Business → Model` 顺序找 `method_exists` 首次命中）。

第一轮 rebase 时按「保留本分支的 96 个显式转发」解掉了 4 个冲突；**随后作者裁定：这两个类应当用 master 的方式（`__callStatic`）嵌入 Helper** —— 于是按 master 版本落回，并同步改写测试。最终落地：

| 冲突文件 | 最终解决 |
|---|---|
| `src/Foundation/Helper.php` | **取 master 版**（27 行：只有 `__callStatic` 派发；无显式方法、无事件属性、无 `SingletonExTrait`） |
| `src/DuckPhpAllInOne.php` | **取 master 版**（121 行：`__callStatic` + 原文件其余部分） |
| `tests/Foundation/HelperTest.php` | 以 master 版为底座扩写：派发顺序/胜出方/逐名冒烟/冲突语义（见阶段二） |
| `tests/DuckPhpAllInOneTest.php` | **取 master 版**（`__callStatic` 断言原样保留） |
| `tests/data_for_tests/ZAllDemoTest.config.php` | 按实测重算 → **10531**（两种并集写法下长度恰好相同） |

> ⚠️ **两种并集写法的行为差异（已核实：除 `ThrowOn` 外都只是「换了转发目标但实现等价」）**
>
> | 重名方法 | 旧 `insteadof` 裁定 | 魔术顺序裁定 | 两版实现 |
> |---|---|---|---|
> | `Setting` `AppOptions` `Config` `XpCall` | Business | **Controller** | 都转到 App/Configer/CoreHelper，等价 |
> | `header` `setcookie` `exit` | Controller | **System** | 逐字相同（`SystemWrapper::_()->_*`） |
> | `FireGlobalEvent` `OnGlobalEvent` | Business | **System** | 三层实现相同（`GlobalEvent::_()->fire()`） |
> | `ThrowOn` | System | System | 唯一语义可辨项（Project 版），两种裁定一致 |
> | `AdminService` `UserService` | Controller | Controller | 一致 |
>
> 这 8 个换主人的名字已被测试钉住（`tests/Foundation/HelperTest.php::CONFLICT_WINNER`），改派发顺序或改四个类的顺序即红。

**随 rebase 一起进本提交的适配**

- `src/Foundation/Model/Base.php`：master 把 `Foundation\ModelTrait` 移到了 `Foundation\Model\ModelTrait`（命名空间也变了），去掉旧的 `use` 导入。
- `tests/DuckPhpAllInOneTest.php`：`__callStatic` 断言 → 「未定义静态方法报 PHP `Error`」。
- `ZAllDemoTest` 的 `files`：master 的类移动使其变成 10532，本分支的 Helper 路径变化再减 1 → **10531**（dump 见 `tests/data_for_tests/ZAllDemoTest-10531.txt`）。

**master 带来的结构性变化（后续阶段必须按新路径写）**

`Foundation\ModelTrait` → `Foundation\Model\ModelTrait`（`namespace DuckPhp\Foundation\Model`）；`Foundation\SessionTrait` → `Foundation\Controller\SessionTrait`；`Foundation\ExceptionReporterTrait` → `Foundation\Controller\ExceptionReporterTrait`；`Core\ThrowOnTrait` → `Ext\ThrowOnTrait`；**`Foundation\ExceptionTrait` 被删除**。另外 `skeleton/src/Controller/Helper.php` 在 master 上已经就是 `extends Foundation\Controller\Helper`（与本分支设计一致），并新增了 `skeleton/src/Controller/AppAction.php`。

**master 遗留的坏引用（不是本分支引入的，本分支也未修）**

| 文件 | 引用 | 状态 |
|---|---|---|
| `demo/src/Controller/Session.php` | `use DuckPhp\Foundation\SessionTrait;`（类体内真的 `use`） | 被加载即致命（master 已把它移走） |
| `demo/src/System/ProjectException.php`、`skeleton/src/System/ProjectException.php` | `use DuckPhp\Foundation\ExceptionTrait;`（类体内真的 `use`） | 被加载即致命（master 已删掉该 trait） |
| `skeleton/src/Controller/AppAction.php`（master 新增） | `use DuckPhp\Foundation\ExceptionReporterTrait;` | 被加载即致命（master 已把它移走） |
| `tests/data_for_tests/Ext/SqlDumper/Model/{Empty,Error,NoTable}Model.php` | `use DuckPhp\Foundation\ModelTrait;` | 被加载即致命（master 已把它移走） |
| `src/Core/DuckPhpSystemException.php` | `use DuckPhp\Core\ThrowOnTrait;` | 未使用的导入，无害 |

> 全量测试仍绿，因为这些文件都不在测试加载路径上。建议单独一轮清理（其中 skeleton 的两处会在阶段三一并处理）；master 的类移动也**没有同步文档**，参考手册里 `Foundation-ModelTrait.md`/`Foundation-SessionTrait.md`/`Foundation-ExceptionReporterTrait.md`/`Core-ThrowOnTrait.md`/`Foundation-ExceptionTrait.md` 都还是旧路径，需在阶段四一并处理或单独立项。

**作者随后的提交（分支继续前进，记下来以免错认归属）**

| 提交 | 内容 |
|---|---|
| `6dfd1972` | 完成代码的更改 —— 把本阶段那批改动提交了（并集改 `__callStatic`、两处测试、本 checklist） |
| `838b42c9` | 调整 GlobalAdmin/GlobalUser —— `mergeViewData()` **不再**渲染 header/footer（回到只做 `addExtViewData`），另改 `src/DuckPhp.php` 4 行 |
| `380a9ba3` | `Command` 的提示由 `_` 改成 `-` |
| `87f8027b` | 修正 `RouteLister`（跳过非 public 方法、`path_info` 补前导 `/`）+ `Ext\PermissionMenu` 两处 |

> ⚠️ **HEAD(`87f8027b`) 目前自带 4 个红灯，与本分支的 Helper 合并无关**。取证：把本 checklist 记录的未提交改动 `git stash` 掉后，纯 HEAD 复现同样结果。
>
> | 测试 | 现象 | 归属 |
> |---|---|---|
> | `tests/GlobalAdmin/GlobalAdminTest.php:55` | `Failed asserting that '' contains "Block"` | `838b42c9`：`mergeViewData()` 不再渲染 header/footer，测试仍按渲染断言 |
> | `tests/GlobalUser/GlobalUserTest.php:63` | 同上 | 同上 |
> | `tests/Ext/PermissionMenuTest.php:93` | 期望 `…\AdminController` 实得 `''` | `87f8027b`：`RouteLister` 的 path 前缀/可见性变化 |
> | `tests/DuckPhp/DuckPhpTest.php:186` | `DuckPhpSystemException: No GlobalUser Provider.` | `838b42c9`（`src/DuckPhp.php` 那 4 行） |
>
> 这 4 个不属于本任务范围，需作者裁定是改测试还是改回源码。

---

## 阶段一 · 修改代码 —— ✅ 已完成

- [x] `src/Foundation/System/Helper.php` ← `AppHelperTrait`（40 方法）
- [x] `src/Foundation/Business/Helper.php` ← `BusinessHelperTrait`（17 + 4 事件属性）
- [x] `src/Foundation/Controller/Helper.php` ← `ControllerHelperTrait`（48 + 6 事件属性）
- [x] `src/Foundation/Model/Helper.php` ← `ModelHelperTrait`（6 方法）
- [x] `src/Foundation/Model/Base.php` → `abstract class Base extends Helper { use ModelTrait; }`，并**显式声明** 6 个数据层静态助手（`Db/DbForRead/DbForWrite/SqlForPager/SqlForCountSimply/DatabaseDriver`）——并集走魔术转发，但模型基类要保持 `$model->Db()` 这种「静态方法经实例调用」可用
- [x] 两个并集类补 **96 条 `@method` 注释**（IDE / PHPStan 可见性）：按派发顺序分组标注来源层，实测分布 System 40 / Controller 42 / Business 8 / Model 6
- [x] `src/Foundation/Helper.php`：删 4 个 `use ...Trait` 与 12 条 `insteadof`，改为 **master 版**——`__callStatic` 按 `System → Controller → Business → Model` 派发到四层 Helper（27 行）
- [x] `src/DuckPhpAllInOne.php`：同上，内联同一套 `__callStatic` 派发（121 行；不再持有 96 个显式方法，也不再持有 10 个事件属性）
- [x] 删除 `src/Helper/`（4 个文件）
- [x] `php -l` 全部改动文件（Windows PHP 7.4.33，顺带兜 7.4 兼容）
- [x] `bash docs/scripts/check-non-ascii.sh` → `Total non-ASCII lines: 0`

**做法记录**

- **文件移动**：`git mv -f src/Helper/<X>HelperTrait.php src/Foundation/<层>/Helper.php`（4 次）——trait 原文落在目标路径后，只改两行：`namespace DuckPhp\Foundation\<层>;` 与 `trait X` → `class Helper`，方法体逐字未动。**四个层 Helper 是方法与事件属性的唯一宿主。**
- **并集**：曾用一次性反射脚本生成 96 个显式转发（`%TEMP%\dph-gen-forwarders.php`，未提交）；作者裁定改用 master 的 `__callStatic` 方案后，该生成物作废（不再需要、也未提交）。
- **事件属性**：10 个 `$EVENT_*` 只留在 Business（4 个）/ Controller（6 个）两个层 Helper 上；并集类按 master 版不再自带（全仓无任何代码引用并集类的这些属性，已在测试 `testLayersHoldTheWholeApi` 里钉住归属）。
- **`_()`**：`Foundation\Helper` 按 master 版不再 `use SingletonExTrait`（全仓无 `Foundation\Helper::_()` 调用）；`DuckPhpAllInOne::_()` 来自父类 `App`。
- **`@method` 注释**：`__callStatic` 方案的固有代价是「反射/IDE/`method_exists()` 看不到方法」，作者要求补注释 → 用一次性反射脚本（`%TEMP%\dph-gen-method-tags.php`，未提交）从**胜出层**的真实签名生成 96 条 `@method static <返回类型> <名字>(<参数>)`，按 `---- resolved from Foundation\<层>\Helper (n) ----` 分组；无返回类型的方法标 `mixed`，`saveExtOptions` 标 `void`。注释只进 docblock，不产生真方法（`testLayersHoldTheWholeApi` 仍断言两个并集类「不声明任何 Helper 方法」）。

## 阶段二 · 测试 —— ✅ 已完成

- [x] 删 `tests/Helper/`：`git mv -f tests/Helper/*TraitTest.php tests/Foundation/<层>/HelperTest.php`（4 次，覆盖原占位测试）
- [x] 4 份行为测试平移到层 Helper 类：`LibCoverage::Begin/End(Helper::class)`、`XxxHelper::` → `Helper::`
- [x] fixture 局部类保留/改名：`HelperFakeSessionHandler`、`HelperTestObject`（`tests\DuckPhp\Foundation\System`）
- [x] Controller 测试的视图数据路径 `__DIR__.'/../'` → `__DIR__.'/../../'`（文件下移一层）
- [x] 删掉两条**本来就不存在**的方法调用（`ControllerHelper::AdminAction()` / `UserAction()`，旧 API 残留，靠 try/catch 静默失败）
- [x] `tests/Foundation/HelperTest.php`（以 master 版为底座扩写）5 个测试：
  - [x] `testLayersHoldTheWholeApi`：四层并集仍恰好 96 个方法；两个并集类**不声明**任何 Helper 方法；10 个事件属性各由一层（Business/Controller）唯一持有
  - [x] `testDispatchOrderAndWinnersArePinned`：解析两个并集类源码，钉住 `$classes` 顺序 = System → Controller → Business → Model，且按顺序算出的 12 个重名胜出方 = `CONFLICT_WINNER` 表
  - [x] `testUnknownMethodRaisesUserError`：未定义方法触发 `trigger_error(E_USER_ERROR)`（master 原断言，两个并集类各测一次）
  - [x] `testSmokeEveryDispatchedMethod`：96 个方法 × 2 个并集类用哑参数真实调用（哑参数按「胜出层同名方法的签名」生成）
  - [x] `testThrowOnConflictTakesProjectVersion`：`ThrowOn` 在并集/AllInOne/System 上取 Project 版，Business/Controller 各取本层版
- [x] `tests/DuckPhpAllInOneTest.php`：保留 master 的 `__callStatic` 断言（`Setting()` 可用 + 未定义方法报错）
- [x] **反向验证**（第一轮「显式转发」方案下做的，该方案已被裁定替换）：删掉一个转发 → 立刻红 → 恢复即绿；作为当时的兜底证据保留
- [x] `ZAllDemoTest` 的 `files` 期望值：10360 → 10359（本分支）→ 10531（并 master + 定稿并集写法），当前 **10531**
- [x] 全量测试绿（定稿时）：`OK (91 tests, 593 assertions)`，覆盖率 `4862/4863 (99.98%)`

### 作者追加要求（阶段尾补，未提交）

- [x] **并集类补 `@method` 注释**（让 IDE / PHPStan 看得到方法）：`src/Foundation/Helper.php`、`src/DuckPhpAllInOne.php` 各 96 条，按派发来源层分组（`---- resolved from Foundation\<层>\Helper (n) ----`），实测 System 40 / Controller 42 / Business 8 / Model 6；无返回类型标 `mixed`，`saveExtOptions` 标 `void`。注释只在 docblock，不产生真方法（`testLayersHoldTheWholeApi` 仍断言两个并集类不声明 Helper 方法）。
- [x] **`Model\Base` 显式声明 6 个数据层助手**（`Db/DbForRead/DbForWrite/SqlForPager/SqlForCountSimply/DatabaseDriver`），把 `$model->Db()` 这类「静态方法经实例调用」恢复回来；`extends Helper` 保留，其余方法仍走 `__callStatic`。
- [x] 回归测试 `tests/Foundation/Model/HelperTest.php::testModelBaseDeclaresHelpersExplicitly`：6 个方法必须是**显式声明**（`getDeclaringClass()` = `Model\Base`）、`is_callable([$model, $name])` 为真（正是魔术转发做不到的那点），再逐个用实例式调用冒烟。
- 本批验收：`tests/Foundation/Model/HelperTest.php` → `OK (2 tests, 19 assertions)`；`tests/Foundation/HelperTest.php` → `OK (5 tests, 34 assertions)`；`tests/DuckPhpAllInOneTest.php` → `OK (1 test, 8 assertions)`；`bash docs/scripts/check-non-ascii.sh` → 0（`src/` 仍纯 ASCII）。

**验收命令与结果**

```powershell
$env:WSL_UTF8=1
# 并集一致性 / 防漂移测试（最终态）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Foundation/HelperTest.php"
#   → OK (5 tests, 34 assertions)
# AllInOne（master 版 __callStatic 断言）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/DuckPhpAllInOneTest.php"
#   → OK (1 test, 8 assertions)
# 整个 Foundation 目录
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Foundation"
#   → OK (21 tests, 477 assertions)
# 第三方应用示例
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"
#   → OK (1 test, 36 assertions)
# 全量（含覆盖率）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && XDEBUG_MODE=coverage php vendor/bin/phpunit"
#   → OK (91 tests, 593 assertions)   Test Lines: 4862/4863(99.98%)
```

**`ZAllDemoTest` 的 `files` 期望值沿革**（该页含「已加载文件清单 + 调用栈」，长度随类文件路径与行号变化）

| 阶段 | 值 | 原因 |
|---|---|---|
| doced 基线 | 10360 | — |
| 本分支 trait 并进 `Foundation\Controller\Helper` | 10359 | 文件路径变短（逐行 diff见下），已取证 |
| 并 master 的类移动 | 10532（master 侧）/ 10531（本分支） | master 把 SessionTrait/ModelTrait/ExceptionReporterTrait 分了目录 |
| **定稿（master 版 `__callStatic` 并集）** | **10531** | 与显式转发写法长度恰好相同，无需再改 |

<details><summary>当时的 10360 → 10359 逐行 diff 取证（历史记录）</summary>

| 行 | 旧（10360） | 新（10359） |
|---|---|---|
| 171 | `#3 /src/Helper/ControllerHelperTrait.php(89): DuckPhp\DuckPhp->_Show()` | `#3 /src/Foundation/Controller/Helper.php(89): DuckPhp\DuckPhp->_Show()` |
| 172 | `…ProjectNameTemplate\Controller\Helper::Show()` | `…DuckPhp\Foundation\Controller\Helper::Show()`（方法改为继承而来，栈帧报声明类） |
| 219 | `35 => '/src/Helper/ControllerHelperTrait.php',` | `35 => '/src/Foundation/Controller/Helper.php',` |

</details>

> 5/7 行（执行耗时/内存）宽度未变，其余路由（`test/done` 95、根页 1363、`doc.php` 1329 等）全部未变。已在 `tests/data_for_tests/ZAllDemoTest.config.php` 就地注释原因。

**与原计划的两处偏差（已按「测试必须能跑」原则调整）**

1. **demo/ 与 `ZThirdDemo` 的 3+3 个 Helper 提前到本阶段改**：它们是 `ZAllDemoTest`/`ZThirdDemoTest` 的被测宿主，不改则阶段二无法验证（实测：`ZThirdDemoTest` 报 `Trait "DuckPhp\Helper\ControllerHelperTrait" not found`、`ZAllDemoTest` 的 `test/done`/根页/`files` 全部空响应）。改为 `extends \DuckPhp\Foundation\<层>\Helper`（`SingletonTrait` 不再需要，`_()` 由父类继承，语义不变）。
2. **`skeleton/` 仍留在阶段三**：它不被任何测试加载（`DuckPhpInstallerTest` 只复制骨架并断言 `src/System/NSXApp.php`），所以全量测试能全绿。**目前全仓仅剩 `skeleton/src/{Controller,Business}/Helper.php`、`skeleton/src/Model/Base.php` 还引用已删除的 trait** —— 提交前请确认这三处已在阶段三处理。

## 阶段三 · skeleton 的引用（含 skeleton 侧文档）—— ✅ 已完成

- [x] `skeleton/src/Controller/Helper.php` —— **master 上已经就是** `extends Foundation\Controller\Helper`（与本分支设计一致，无需再改）
- [x] `skeleton/src/Business/Helper.php` → `class Helper extends \DuckPhp\Foundation\Business\Helper`（保留「Don't change me」注释与 `// your helper methods` 位）
- [x] `skeleton/src/Model/Base.php` → `class Base extends \DuckPhp\Foundation\Model\Base`（ModelTrait 与 6 个数据层助手都由父类继承）
- [x] `skeleton/src/Controller/AppAction.php`：`use DuckPhp\Foundation\ExceptionReporterTrait;` → `Foundation\Controller\ExceptionReporterTrait`（master 遗留坏引用）
- [x] `skeleton/src/System/ProjectException.php`：删掉已废弃的 `ExceptionTrait`（master 已删除该 trait）；类保持 `extends \Exception`，与 master 对 `UserException`/`AdminException` 的处理一致
- [x] `skeleton/agent-zh.md`：§Model 数据连接方法（改成「父类自带」）、异常类示例（去掉 `ExceptionTrait`）、`ThrowOn` 示例（改成 `Helper::BusinessThrowOn/ControllerThrowOn`，并给出 `DuckPhp\Ext\ThrowOnTrait` 的自助写法）、异常报告器 `use` 路径；`RULES.md` 无需改（无 trait 措辞）
- [x] **顺带修掉 master 的其它遗留坏引用**：`demo/src/Controller/Session.php`（`Foundation\SessionTrait` → `Foundation\Controller\SessionTrait`）、`demo/src/System/ProjectException.php`（删 `ExceptionTrait`）、`tests/data_for_tests/Ext/SqlDumper/Model/{Empty,Error,NoTable}Model.php`（`Foundation\ModelTrait` → `Foundation\Model\ModelTrait`）、`src/Core/DuckPhpSystemException.php`（删掉指向已移走的 `Core\ThrowOnTrait` 的无用导入）
- [x] **额外发现并修掉一个骨架致命 bug**：`skeleton/src/System/App.php` 的 `protected function onInited()` **少了 `: void`**，与父类 `KernelTrait::onInited(): void` 不兼容 → 每个新装项目的 `App` 类**一加载就 Fatal error**（doced 时代就存在，测试没覆盖到，因为安装器测试只看文件内容不加载类）。已补 `: void`
- [x] 加护栏 `tests/Ext/DuckPhpInstallerTest.php`：注册 `NSX\` 自动加载后逐个 `class_exists()` 15 个生成出来的骨架类（含 `NSXApp`），并断言 `Model\Base` 的 `Db`/`DatabaseDriver` 可经实例调用
- 备注：`ZThirdDemo` 已在阶段二完成
- 验收：
  - `grep -rn "HelperTrait" skeleton demo tests` → **0 处**；「已删除/已移动的类」全仓残留引用 → **0 处**
  - 独立脚本把 `skeleton/src` 下 15 个类全部 `class_exists()` 通过 + 6 个模型助手 `is_callable([$model,...])` 为真 → `ALL OK`
  - `DuckPhpInstallerTest` → `OK (1 test, 9 assertions)`；**反向验证**：把 `onInited()` 的 `: void` 去掉 → 立刻 `PHP Fatal error: Declaration of NSX\System\NSXApp::onInited() must be compatible…`（护栏有效）→ 恢复即绿
  - 全量：`OK (92 tests, 622 assertions)`，覆盖率 `4876/4884 (99.84%)`
- ⚠️ **新发现的文档漂移（交给阶段四/五）**：`use_admin_view_header_footer` / `use_user_view_header_footer` 这两个选项**源码里已经没有任何读取点**（真正起作用的是 `__logined_enable_header_footer`），但参考手册 `GlobalAdmin-GlobalUser`/`options*.md` 仍在写它们，且 `docs/scripts/gen-options-docs.php:52-53` 把这两行**硬编码**在生成器里 → 两个测试里也还在设它们（无害但误导）。阶段四需要一并处理，否则重新生成索引又会把它们写回来。

## 阶段四 · 参考手册（`docs/zh/reference/`）

- [ ] 4 个 `Helper-*HelperTrait.md` 的方法表并入 `Foundation-*-Helper.md`（脚本抽取），删 4 页
- [ ] `Foundation-Helper.md` 重写：**并集 = `__callStatic` 派发**（写清查找顺序 System → Controller → Business → Model 与 12 个重名方法的胜出方），方法列表只需列 `__callStatic` 一条 + 链到四层页
- [ ] `DuckPhpAllInOne.md`：删掉「use 了四个 Helper Trait / insteadof」的旧描述，改为「`__callStatic` 派发到四层 Helper」，方法列表保持 master 状态（本类没有 Helper 方法条目）
- [ ] `Foundation-ModelTrait.md` / `Foundation-Model-Base.md` / `Foundation-Controller-Base.md` 等互链
- [ ] `Foundation-Model-Base.md`：补 6 个**显式声明**的静态助手方法条目（`Model\Base` 现在真声明了它们，漂移扫描会要求）；同时把「同时使用 `ModelTrait` + `ModelHelperTrait`」的描述改成「继承 `Model\Helper` 派生的 `Helper` 并显式声明 6 个助手」
- [ ] `Foundation-Helper.md` / `DuckPhpAllInOne.md`：说明「方法由 `__callStatic` 派发，源码里带 96 条 `@method` 注释供 IDE/静态分析使用」
- [ ] master 的类移动未同步文档：`Foundation-ModelTrait.md`、`Foundation-SessionTrait.md`、`Foundation-ExceptionReporterTrait.md`、`Core-ThrowOnTrait.md`、`Foundation-ExceptionTrait.md` 需改名/改写或单独立项
- [ ] **清掉已失效的选项**：`use_admin_view_header_footer` / `use_user_view_header_footer` 源码里已无读取点 → 从 `docs/scripts/gen-options-docs.php:52-53` 的硬编码表里删掉，并同步 `GlobalAdmin-GlobalAdmin.md`/`GlobalUser-GlobalUser.md` 的「隐藏选项」段（重新生成 `options.md`/`options-by-class.md` 后旧行会消失）；两个测试里设置它们的语句同时删掉
- [ ] `php docs/scripts/gen-options-docs.php` 重生成索引（类页 113 → 109）+ `--check` 通过
- 验收：`python3 docs/scripts/check-doc-links.py docs/zh` → `broken: 0`；漂移扫描 `missing-method` 为空；`grep -rn "HelperTrait" docs/zh/reference` = 0

## 阶段五 · 用户指南（`docs/zh/guide/`）

- [ ] `helper.md` 重写（表格第 4 列、最小示例改 `extends`、§3 改「继承即边界」、常见写法/错误表/链接）
- [ ] `helper.md` 里补一句并集类的定位：`Foundation\Helper` / `DuckPhpAllInOne` 用 `__callStatic` 按 System → Controller → Business → Model 派发，源码带 `@method` 注释；`Model\Base` 的 6 个数据层助手是显式声明（`$model->Db()` 可用）
- [ ] `layers.md`、`controllers.md`、`model.md`、`quickstart.md`、`events.md`、`embed.md`、`validator.md`、`security-performance.md`、`lifecycle.md`、`cache.md`
- [ ] 修掉 `helper.md` 里不存在的 `ZAllDemo/src/Controller/Helper.php` 引用
- [ ] 账本：`guide-maintenance-guide.md`（§1 例子 + M7 记录）、`guide-rewrite-checklist.md`、`reference-maintenance-guide.md`
- 验收：链接检查 0；`grep -rn "HelperTrait" docs/zh/guide` = 0（历史沿革段除外）

## 提交（由作者执行）

- [x] 阶段一＋二 已提交（作者 `6dfd1972`，并集改 master 方案）
- [x] 作者后续提交：`838b42c9` / `380a9ba3` / `87f8027b`
- [x] 阶段尾补（@method 注释 + `Model\Base` 显式 6 方法 + 回归测试）→ **`3d78611b`**
- [x] 4 个测试红灯修复（GlobalAdmin/GlobalUser header/footer 丢失 + 4 个测试口径同步）→ **`bef4d50e`**（全量 `OK (92 tests, 619 assertions)`）
- [ ] 阶段三（skeleton / demo / 测试数据引用 + 骨架 `onInited(): void` 致命 bug + 安装器护栏）—— **已 staged，未提交**
- [ ] 阶段四（参考手册）
- [ ] 阶段五（用户指南 + 账本）

## 风险与备忘

- 并集是 `__callStatic` 魔术派发：**反射 / `method_exists()` 看不到并集类的方法**（这正是它的取舍）——已按作者要求补 96 条 `@method` 注释给 IDE/PHPStan 看，并把「96 个名字都能派发到真实实现」「派发顺序」「12 个重名胜出方」写成显式测试钉住（`tests/Foundation/HelperTest.php`）。注意 `@method` 只是注释：`is_callable()`、`method_exists()`、`ReflectionMethod` 仍然看不到它们。
- 体积（定稿）：`Foundation/Helper.php` 35 → **27 行**；`DuckPhpAllInOne.php` 116 → **121 行**；四个层 Helper 各自持有方法体（190–249 行）。对比显式转发写法（425 / 511 行）更瘦，且全量覆盖率从 98.13% 升到 99.98%。
- `ZAllDemoTest` 的 `files` 期望值含执行耗时/内存字样，其**宽度**变化会再改长度（既有脆弱点，本次未动）。
- 一次性生成脚本 `%TEMP%\dph-gen-forwarders.php` 未提交，**已作废**（并集不再用显式转发）。
- `composer.json` 版本号默认不动（1.4.1）。
- 不动：`docs/en/`、`docs/old/`、`docs/duckphp.gv`（陈旧生成物）、`README*.md`。
- 未跟踪的 `CODING_MEMO.md` 目录树仍写 `Helper/`、`tests/Helper/`，需作者点头才动。
- 测试数据目录 `tests/data_for_tests/Helper/`（`ControllerHelper/`、`BusinessHelper/`、`ViewHelper/`）**故意没改名**：`tests/Core/AppTest.php:461` 也在用同一份 fixture，改名要动那个文件，留待需要时一起整理。
