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
| 事件属性 | Business 4 个 + Controller 6 个；并集类今天靠 trait 白拿，必须原样保留 |
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

## 阶段一 · 修改代码 —— ✅ 已完成

- [x] `src/Foundation/System/Helper.php` ← `AppHelperTrait`（40 方法）
- [x] `src/Foundation/Business/Helper.php` ← `BusinessHelperTrait`（17 + 4 事件属性）
- [x] `src/Foundation/Controller/Helper.php` ← `ControllerHelperTrait`（48 + 6 事件属性）
- [x] `src/Foundation/Model/Helper.php` ← `ModelHelperTrait`（6 方法）
- [x] `src/Foundation/Model/Base.php` → `abstract class Base extends Helper { use ModelTrait; }`
- [x] `src/Foundation/Helper.php`：删 4 个 `use ...Trait` 与 12 条 `insteadof`；加 96 个转发 + 10 个事件属性 + `use SingletonExTrait`（425 行）
- [x] `src/DuckPhpAllInOne.php`：同上，96 个转发指向 `Foundation\Helper`（116 → 511 行）
- [x] 删除 `src/Helper/`（4 个文件）
- [x] `php -l` 全部改动文件（Windows PHP 7.4.33，顺带兜 7.4 兼容）
- [x] `bash docs/scripts/check-non-ascii.sh` → `Total non-ASCII lines: 0`

**做法记录**

- **文件移动**：`git mv -f src/Helper/<X>HelperTrait.php src/Foundation/<层>/Helper.php`（4 次）——trait 原文落在目标路径后，只改两行：`namespace DuckPhp\Foundation\<层>;` 与 `trait X` → `class Helper`，方法体逐字未动。
- **并集生成**：一次性反射脚本（`%TEMP%\dph-gen-forwarders.php`，**不提交**）读四层 Helper 的 public static 方法，按冲突表选目标，生成两处各 96 个转发 + 10 个事件属性；脚本自带校验「并集 96 / 冲突 12」不满足即报错。
- 分层注释块：`////////// Model|Business|Controller|System layer (…) //////////`。
- 已知取舍：`Foundation\Helper` 与 `DuckPhpAllInOne` 的 96 个转发是**有意重复**（方案 A 的代价），由阶段二的反射测试兜住同步。

## 阶段二 · 测试 —— ✅ 已完成

- [x] 删 `tests/Helper/`：`git mv -f tests/Helper/*TraitTest.php tests/Foundation/<层>/HelperTest.php`（4 次，覆盖原占位测试）
- [x] 4 份行为测试平移到层 Helper 类：`LibCoverage::Begin/End(Helper::class)`、`XxxHelper::` → `Helper::`
- [x] fixture 局部类保留/改名：`HelperFakeSessionHandler`、`HelperTestObject`（`tests\DuckPhp\Foundation\System`）
- [x] Controller 测试的视图数据路径 `__DIR__.'/../'` → `__DIR__.'/../../'`（文件下移一层）
- [x] 删掉两条**本来就不存在**的方法调用（`ControllerHelper::AdminAction()` / `UserAction()`，旧 API 残留，靠 try/catch 静默失败）
- [x] `tests/Foundation/HelperTest.php` 新增 5 个防漂移测试：
  - [x] `testForwardTargetsMatchConflictTable`：解析 `src/Foundation/Helper.php` 源码，逐方法核对转发目标 = 应转发层（12 个冲突项按上表）
  - [x] `testSurfaceAndSignaturesMatchLayers`：方法集**恰等于**四层并集，且**每个方法签名**（参数名/类型/默认值/可变参数/返回类型）与源层一致
  - [x] `testAllInOneCarriesWholeUnion`：`DuckPhpAllInOne` 含全部并集方法，签名同样逐一比对
  - [x] `testEventPropertiesSurviveOnBothUnions`：10 个事件属性在两个并集类上都存在且与层 Helper 值一致
  - [x] `testSmokeEveryForwarder`：96 个方法 × 2 个并集类用哑参数逐个真实调用（先替换 `header/setcookie/exit` 包装器）
  - [x] `testThrowOnConflictTakesProjectVersion`：唯一「语义可辨」的冲突项 —— `ThrowOn` 在并集/AllInOne/System 上取 Project 版，Business/Controller 各取本层版
- [x] **反向验证**：临时删掉 `Foundation\Helper::Db()` 一个转发 → 测试立刻红（`Tests: 6, Assertions: 183, Errors: 1, Failures: 2`，其中 `Foundation\Helper 少了方法 … 0 => 'Db'`）→ 从备份恢复 → 复跑回到 `OK (6 tests, 445 assertions)`
- [x] 重算 `ZAllDemoTest`：`files` **10360 → 10359**（逐行 diff 取证见下）
- [x] 全量测试绿：`OK (94 tests, 1005 assertions)`，覆盖率 `4912/5007 (98.10%)`

**验收命令与结果**

```powershell
$env:WSL_UTF8=1
# 并集防漂移测试
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Foundation/HelperTest.php"
#   → OK (6 tests, 445 assertions)
# 整个 Foundation 目录
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Foundation"
#   → OK (21 tests, 477 assertions)
# 第三方应用示例
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"
#   → OK (1 test, 36 assertions)
# 全量（含覆盖率）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && XDEBUG_MODE=coverage php vendor/bin/phpunit"
#   → OK (94 tests, 1005 assertions)   Test Lines: 4912/5007(98.10%)
```

**`ZAllDemoTest` 期望值变化取证**（新旧 dump 逐行 diff，仅 3 行实质变化）

| 行 | 旧（10360） | 新（10359） |
|---|---|---|
| 171 | `#3 /src/Helper/ControllerHelperTrait.php(89): DuckPhp\DuckPhp->_Show()` | `#3 /src/Foundation/Controller/Helper.php(89): DuckPhp\DuckPhp->_Show()` |
| 172 | `…ProjectNameTemplate\Controller\Helper::Show()` | `…DuckPhp\Foundation\Controller\Helper::Show()`（方法改为继承而来，栈帧报声明类） |
| 219 | `35 => '/src/Helper/ControllerHelperTrait.php',` | `35 => '/src/Foundation/Controller/Helper.php',` |

> 5/7 行（执行耗时/内存）宽度未变，其余路由（`test/done` 95、根页 1363、`doc.php` 1329 等）全部未变 ⇒ 长度差 1 字节完全由文件路径变化解释。已在 `tests/data_for_tests/ZAllDemoTest.config.php` 就地注释原因。

**与原计划的两处偏差（已按「测试必须能跑」原则调整）**

1. **demo/ 与 `ZThirdDemo` 的 3+3 个 Helper 提前到本阶段改**：它们是 `ZAllDemoTest`/`ZThirdDemoTest` 的被测宿主，不改则阶段二无法验证（实测：`ZThirdDemoTest` 报 `Trait "DuckPhp\Helper\ControllerHelperTrait" not found`、`ZAllDemoTest` 的 `test/done`/根页/`files` 全部空响应）。改为 `extends \DuckPhp\Foundation\<层>\Helper`（`SingletonTrait` 不再需要，`_()` 由父类继承，语义不变）。
2. **`skeleton/` 仍留在阶段三**：它不被任何测试加载（`DuckPhpInstallerTest` 只复制骨架并断言 `src/System/NSXApp.php`），所以全量测试能全绿。**目前全仓仅剩 `skeleton/src/{Controller,Business}/Helper.php`、`skeleton/src/Model/Base.php` 还引用已删除的 trait** —— 提交前请确认这三处已在阶段三处理。

## 阶段三 · skeleton 的引用（含 skeleton 侧文档）

- [ ] `skeleton/src/{Controller,Business}/Helper.php` → `extends \DuckPhp\Foundation\<层>\Helper`（保留「Don't change me」注释）
- [ ] `skeleton/src/Model/Base.php` → `extends \DuckPhp\Foundation\Model\Base`
- [ ] `skeleton/agent-zh.md`、`skeleton/RULES.md` 去掉 trait 措辞
- 备注：demo 与 `ZThirdDemo` 已在阶段二完成
- 验收：`grep -rn "HelperTrait" skeleton` = 0；`DuckPhpInstallerTest` 绿

## 阶段四 · 参考手册（`docs/zh/reference/`）

- [ ] 4 个 `Helper-*HelperTrait.md` 的方法表并入 `Foundation-*-Helper.md`（脚本抽取），删 4 页
- [ ] `Foundation-Helper.md` 重写（聚合语义 + 冲突消解表 + 96 条转发条目）
- [ ] `DuckPhpAllInOne.md` 简介措辞 + 96 条方法条目
- [ ] `Foundation-ModelTrait.md` / `Foundation-Model-Base.md` / `Foundation-Controller-Base.md` 等互链
- [ ] `php docs/scripts/gen-options-docs.php` 重生成索引（类页 113 → 109）+ `--check` 通过
- 验收：`python3 docs/scripts/check-doc-links.py docs/zh` → `broken: 0`；漂移扫描 `missing-method` 为空；`grep -rn "HelperTrait" docs/zh/reference` = 0

## 阶段五 · 用户指南（`docs/zh/guide/`）

- [ ] `helper.md` 重写（表格第 4 列、最小示例改 `extends`、§3 改「继承即边界」、常见写法/错误表/链接）
- [ ] `layers.md`、`controllers.md`、`model.md`、`quickstart.md`、`events.md`、`embed.md`、`validator.md`、`security-performance.md`、`lifecycle.md`、`cache.md`
- [ ] 修掉 `helper.md` 里不存在的 `ZAllDemo/src/Controller/Helper.php` 引用
- [ ] 账本：`guide-maintenance-guide.md`（§1 例子 + M7 记录）、`guide-rewrite-checklist.md`、`reference-maintenance-guide.md`
- 验收：链接检查 0；`grep -rn "HelperTrait" docs/zh/guide` = 0（历史沿革段除外）

## 提交（由作者执行）

- [ ] ① 阶段一＋二（代码 + 测试，必须一起绿）
- [ ] ② 阶段三（skeleton）
- [ ] ③ 阶段四（参考手册）
- [ ] ④ 阶段五（用户指南 + 账本）

> 阶段一＋二目前已是「全量绿」状态，且改动**已全部 `git add`**（`src`、`tests/Foundation`、`tests/data_for_tests/ZThirdDemo`、`tests/data_for_tests/ZAllDemoTest.config.php`、`demo/src`、本文件）。
> ⚠️ 注意：若要现在提交 ①，请先做 ②（skeleton 还引用着已删除的 trait）；或者把 ①②合并提交。

## 风险与备忘

- 并集 96 个转发漂移 → 反射集合相等 + 签名逐项 + 转发目标源码核对 + 反向验证四重兜底。
- 体积：`DuckPhpAllInOne.php` 116 → 506 行；`Foundation/Helper.php` 35 → 424 行（方案 A 的既定代价）。
- `ZAllDemoTest` 的 `files` 期望值含执行耗时/内存字样，其**宽度**变化会再改长度（既有脆弱点，本次未动）。
- 生成脚本 `%TEMP%\dph-gen-forwarders.php` 未提交，需要时可重跑（会整文件重写两处并集）。
- `composer.json` 版本号默认不动（1.4.1）。
- 不动：`docs/en/`、`docs/old/`、`docs/duckphp.gv`（陈旧生成物）、`README*.md`。
- 未跟踪的 `CODING_MEMO.md` 目录树仍写 `Helper/`、`tests/Helper/`，需作者点头才动。
- 测试数据目录 `tests/data_for_tests/Helper/`（`ControllerHelper/`、`BusinessHelper/`、`ViewHelper/`）**故意没改名**：`tests/Core/AppTest.php:461` 也在用同一份 fixture，改名要动那个文件，留待需要时一起整理。
