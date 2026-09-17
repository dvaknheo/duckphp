# 参考手册维护指南（给接手文档工作的 AI）

> 目标读者：接手维护 `docs/zh/reference/` 中文参考手册的 AI/工程师。
> 一句话任务：**让 `docs/zh/reference/` 下每篇逐类文档与 `src/` 当前源码保持一致**（方法、选项、类声明、行为描述）。

---

## 1. 事实来源与格式基线

- **唯一事实来源是 `src/` 源码**。文档中的每个方法签名、选项键、常量、类声明都必须能在源码中逐字找到。拿不准就打开源码看，不要凭文档旧文推断。
- **格式基线是 `docs/zh/reference/Core-Route.md`**：新写/重写任何一篇时，先打开它照抄版式（章节顺序、选项表、方法列表的排版），只替换内容。
- 不要引入新风格（如把方法列表改成表格、把“全部选项”改成解析后的键值表）。要与既有 110 篇逐类文档一致。

## 2. 目录与命名映射

| 源码 | 文档 |
|---|---|
| `src/Core/Route.php` | `docs/zh/reference/Core-Route.md` |
| `src/GlobalUser/UserSessionTrait.php` | `docs/zh/reference/GlobalUser-UserSessionTrait.md` |
| `src/DuckPhp.php`（根级文件） | `docs/zh/reference/DuckPhp.md` |

规则：去掉 `src/` 与 `.php`，路径分隔符 `/` 换成 `-`。当前共 110 篇逐类文档 + 4 个汇总页（`index.md`、`options.md`、`options-by-class.md`、`options-index.md`，**不在逐类文档范围内**）。

## 3. 单篇结构模板（与 Core-Route.md 一致）

```
# DuckPhp\Core\FooBar                ← H1：类全名（含命名空间）

## 简介                               ← 2~5 句：角色、典型用法、与谁协作
## 类信息                             ← 命名空间 / 声明（class|abstract class|interface|trait、extends、implements）/ 使用的 Trait / 常量
## 选项                               ← 有 $options 时：| 选项 | 默认值 | 说明 |，**一行一个键**
## 使用方式                           ← 可运行的示例代码
## 配置示例                           ← 可选
## 注意事项                           ← 易错点、源码里的特殊行为（如实写）
## 全部选项                           ← 有 $options 时必须有：```php 块，逐行 `'key' => 值,`
## 方法列表
### 公共方法
    public static function MyMethod(string $a = null): array
一句话说明（做什么 / 何时被调用 / 返回什么）。

    public function _MyMethod(string $a = null)
一句话说明。
### 受保护方法                        ← 没有就整节省略；私有方法同理
## 相关链接                           ← 只链真实存在的文件
```

**方法条目的硬性格式**（校验脚本靠它识别，务必照做）：

- 签名行以 **4 个空格** 缩进，且必须含 `function` 关键字：
  `    public static function Foo($a = null)`（✅）/ `    public static Foo($a = null)`（❌ 会被判为“缺失”）
- 签名行下一行是**一句中文说明**；然后一个空行，再接下一条。
- 静态壳与实例实现分别成条（如 `Url(...)` 与 `_Url(...)`），不要合并成一行写。
- `use` 引入的 Trait 带来的方法**不重复列**（如 `ComponentBase` 不列 `SingletonExTrait::_()`），在正文说明并链到对应文档。
- 引用返回方法照源码写 `&`：`    public function &_GLOBALS(string $k, $v = null)`。

**选项的两处表达**：

- `## 选项` 表格：`| \`key\` | \`默认值\` | 说明 |`，**一行一个键**（不要把多个键塞进同一行）。
- `## 全部选项`：```php 代码块，逐行 `'key' => 值,`，值得与源码一致（源码里被 `//` 注释掉的项可在块内保留为注释并加一句说明）。

## 4. 写作与编码规范

- 语言：说明文字用**简体中文**；类名/方法名/选项名/代码/路径**保留原文**，不翻译。
- 编码：**UTF-8 无 BOM**。（仓库里有历史 GBK 文件；若 `python` 读取报 `UnicodeDecodeError: 0xbc`，说明该文件是 GBK，需用 UTF-8 重写。）
- 行尾：跟随原文件（不强求统一）。
- **`src/` 禁止非 ASCII 字符（硬性规则）**：所有 PHP 源码的注释、字符串、标点一律用 ASCII 英文；不得出现中文、全角标点（`（）！，：；` 等）、全角空格或 emoji。`docs/` 下的中文文档不受此限。
  - 原因：框架源码统一英文注释，保证 PHP 7.4 / 8.4 与各终端下的编码一致性（历史上清理过一轮，新增代码如 `HttpServer` 的 `workers` 注释曾再次引入）。
  - 检查命令：`bash scripts/check-non-ascii.sh`（等价于 `grep -rnP '[^\x00-\x7F]' src/ --include='*.php'`）。**必须在 WSL 下跑**（Windows 侧没有 bash）。
  - 判定：输出 **`Total non-ASCII lines: 0`** 才算通过——该脚本**不设置失败退出码**，不要只看 `$LASTEXITCODE`。
  - 命中后：把命中行改写成英文 ASCII 注释，再重跑确认清零。
- **不虚构**：源码没写的机制不要编；源码里的“怪癖/不一致”（如某方法赋值顺序特殊、某接口参数拼写 `$contetxt`）要如实在“注意事项”里说明，并注明“以源码为准”。

## 5. 工具

| 工具 | 用途 |
|---|---|
| `scripts/gen-reference.php` | `facts <src-rel>` 打印解析结果；`skeleton [--out DIR] [--file REL]` 生成骨架；`verify --file <md>` 比对方法/选项 |
| `scripts/gen-route.php` | 极简版骨架生成（Route 风格，只抓声明/方法行/options 原文） |

> ⚠️ **`gen-reference.php verify` 不可全信**：对 `Core/App.php` 这类“`use KernelTrait { … as … }` 并 override”的大文件，它可能漏列方法，从而把正确文档误报为“多了方法”。判定一致性请以下面第 6 节的**漂移扫描**为准。

### 漂移扫描脚本（推荐，复制到临时文件运行）

它把“文档 vs 源码”的方法集/选项集做集合比对，只报差异。保存为 `%TEMP%\drift.py` 后运行 `python %TEMP%\drift.py`：

```python
import re, os, sys, subprocess, glob

SRC, REF = 'src', 'docs/zh/reference'

def src_methods(p):
    php = open(p, encoding='utf-8').read()
    return set(re.findall(r'^\s*(?:public|protected|private)\s+(?:static\s+)?function\s+&?\s*(\w+)\s*\(', php, re.M))

def doc_methods(p):
    txt = open(p, encoding='utf-8').read()
    return set(re.findall(r'^\s+(?:public|protected|private)\s+(?:static\s+)?function\s+&?\s*(\w+)\s*\(', txt, re.M))

def src_opts(p):
    php = open(p, encoding='utf-8').read()
    m = re.search(r'\$options\s*=\s*\[(.*?)\n\s*\];', php, re.S)
    if not m: return None
    keys = []
    for line in m.group(1).split('\n'):
        s = line.strip()
        if s.startswith('//'): continue
        if len(line) - len(line.lstrip()) > 8: continue   # 只取顶层键，忽略嵌套数组里的键
        mm = re.match(r"'([^']+)'\s*=>", s)
        if mm: keys.append(mm.group(1))
    return set(keys)

def doc_opts(p):
    txt = open(p, encoding='utf-8').read()
    return set(re.findall(r'^\|\s*`([A-Za-z_][\w-]*)`\s*\|', txt, re.M))

def md_for(src_rel):
    return REF + '/' + src_rel.replace('src/', '').replace('\\', '/')[:-4].replace('/', '-') + '.md'

# 默认扫描「自某 tag 以来改动的 src 文件」；也可传 --all 扫全量
tag = sys.argv[1] if len(sys.argv) > 1 and sys.argv[1] != '--all' else None
if tag:
    out = subprocess.run(['git', 'diff', '--name-only', tag, 'HEAD', '--', 'src'],
                         capture_output=True, text=True, encoding='utf-8').stdout
    files = [f.strip() for f in out.splitlines() if f.strip() and f.endswith('.php')]
else:
    files = [('src/' + os.path.relpath(p, SRC)).replace('\\', '/') for p in glob.glob(SRC + '/**/*.php', recursive=True)]

for f in sorted(files):
    md = md_for(f)
    if not os.path.exists(md):
        print('NEW-DOC-NEEDED  %-50s -> %s' % (f, os.path.basename(md))); continue
    s, d = src_methods(f), doc_methods(md)
    miss, extra = sorted(s - d), sorted(d - s)
    so = src_opts(f)
    omiss = sorted(so - doc_opts(md)) if so is not None else []
    oextra = sorted(doc_opts(md) - so) if so is not None else []
    tagline = 'UPDATE' if (miss or omiss or oextra) else 'ok'
    print('%-6s %-50s missing-method=%s missing-option=%s extra-option=%s' % (tagline, f, miss, omiss, oextra))
    if extra:
        print('        extra-method=%s' % (extra,))  # 多为「使用方式」示例里自定义的方法，通常无需处理
```

**判读**：`missing-method` / `missing-option` / `extra-option` 非空 = 文档确实缺内容或写多了选项，必须处理；`extra-method`（现由脚本一并打印）通常是「使用方式」示例里的自定义方法，忽略。同一键合并成一行（如 `| \`a\` / \`b\` |`）会被 doc_opts 漏读 → 假报 `missing-option`，拆成单键行即可。

> ⚠️ **本会话环境注意**：DSH 沙箱里 `$env:TEMP` 指向私有临时目录，与 `write` 工具写的 `%TEMP%` 不是同一个；跑脚本请给**绝对路径**（如 `python "C:\Users\<你>\AppData\Local\Temp\drift.py" doced`），否则报 `No such file or directory`。

### 运行环境：测试与 `bash` 脚本走 WSL（硬性）

**PHPUnit 与 `scripts/*.sh` 一律在 WSL 下执行**，不要在 Windows 侧直接跑 `php vendor/bin/phpunit`。

**默认只跑与改动相关的「单个测试文件」**——`phpunit.xml` 开了 `processIsolation="true"`，跑一个目录或全量都很慢（实测单文件约 2 秒，`tests/Component` 整目录约 36 秒，全量更久），没必要不要跑。

```powershell
# 建议先设一次，避免 wsl.exe 输出 UTF-16 乱码（否则中文/结果全成方块）
$env:WSL_UTF8=1

# 改哪个类就测哪个类的测试文件（秒级）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Component/CommandTest.php"

# 需要时按方法名再收窄
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage --filter testAll tests/Component/CommandTest.php"

# 硬性规则脚本
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && bash scripts/check-non-ascii.sh"

# 只在「大改 / 要交差」时才跑目录或全量（很慢，建议放后台）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Component"
```

- 测试文件与被测类的对应：`src/A/B.php` → `tests/A/BTest.php`（如 `src/Component/Command.php` → `tests/Component/CommandTest.php`）。
- 本机环境实测：WSL（Debian）**PHP 8.2.3, NTS**，`php -m` 含 **redis** 扩展；Windows 侧 PHP **没有 redis 扩展**，于是 `RedisCacheTest` / `RedisManagerTest` 会报 `Error: Class 'Redis' not found`——**那是环境假失败，不是代码问题**（WSL 下 `tests/Component` 实测 `OK (17 tests, 123 assertions)`）。
- 项目路径在 WSL 下是 `/mnt/e/ProjectGoat/DNMVCS`。
- `drift.py` 也可在 WSL 下用 `python3` 跑（如 `wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 /mnt/c/Users/<你>/AppData/Local/Temp/drift.py doced"`），结果与 Windows 侧一致。

## 6. 标准工作流

1. **找漂移**
   ```powershell
   git diff --name-only <上次基线tag> HEAD -- src      # 或直接 python %TEMP%\drift.py --all 看全量
   python %TEMP%\drift.py <上次基线tag>
   ```
2. **逐个改动文件更新文档**（按第 3 节模板）：
   - 新增方法 → 在方法列表按**可见性分组**补条目（签名 + 一句说明）；新增选项 → 选项表与「全部选项」块都补；类声明变化（`implements`/`extends`/`use`）→ 改「类信息」；行为变化 → 改对应方法说明与「注意事项」。
   - 删/改名 → 同步删除或改名，并在说明里注明（如 `urlForRegist` → `urlForRegister`、`user_url_regist` → `user_url_register`）。
3. **校验**：重跑 `drift.py` 确认 `missing-*` 为空；再抽查新段落。若本次也改了 `src/`（或 `tests/`）代码：
   - **测试一律在 WSL 下跑，且按「单个测试文件」快速验证**（见第 5 节末的“运行环境”说明）：改 `src/A/B.php` 就测 `tests/A/BTest.php`，如 `wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Component/CommandTest.php"`；全量很慢，没必要时别跑；
   - **必须再跑** `bash scripts/check-non-ascii.sh` 并确认输出 `Total non-ASCII lines: 0`（见第 4 节的硬性规则）。
4. **登记**：勾掉/更新本指南第 8 节的“状态与待办”（若有新增文档，也在第 8 节记一句改动清单即可）。
5. **提交**：只 add 相关路径
   ```powershell
   git add docs/zh/reference
   git commit -m "docs: sync reference with src (<tag>..HEAD)"
   ```
   ⚠️ 仓库根有一批与本工作无关的未跟踪目录（`.obsidian/`、`vendor-bak/`、`bin/*.php`、`reasonix.toml` 等），**不要 `git add .`**。还有更隐蔽的一处：`docs/zh/reference/` 和 `docs/zh/guide/` **各自下面都有一个未跟踪的 `.obsidian/`**（Obsidian 配置），`git add docs/zh/reference` 会把它们一并带走。提交前务必复核：
   ```powershell
   git add docs/zh/reference docs/zh/reference-maintenance-guide.md
   git status --short                 # 确认没有 .obsidian/ 等无关项
   git show --stat HEAD               # 提交后再看一眼文件清单
   # 若已被带入：git rm -r --cached docs/zh/reference/.obsidian; git commit --amend --no-edit
   ```

## 7. 常见陷阱（都已踩过）

| 陷阱 | 说明 |
|---|---|
| 方法行漏写 `function` | 生成成 `protected Foo(): array` 会被校验判为“缺失”。必须是 `protected function Foo(): array`。 |
| 选项表多键合并一行 | 会让自动校验假报缺键；**一行一个键**。 |
| 多行方法签名 | 如 `listAll(bool $a = true,` 换行续参，续行不缩进为签名行，只首行算条目。 |
| 引用返回 `function &name` | 签名照源码写 `&`，校验正则已兼容。 |
| 示例代码里的自定义方法 | 如 `action_index`、`view_hello`、`getList` 等出现在「使用方式」示例里，会被扫描算作 `extra`，属正常，不用删。 |
| 空类/空接口 | 方法列表写“本类为空类，未额外声明方法（继承 … 的能力）”，不要凭空造方法。 |
| Trait 转发类 | 如 `Foundation\Controller\Helper`（`use ControllerHelperTrait`）不重复列 46 个方法，只说明“方法全部由某 Trait 提供”，并链到该 Trait 文档。 |
| GBK 旧文件 | 读取报 `0xbc` 即 GBK，需以 UTF-8 重写整篇。 |
| PowerShell 写中文 | 禁止 `Set-Content`/`Out-File` 直接写中文（会乱码）；用编辑器/`write_file` 工具写 UTF-8，或 `python` 显式 `encoding='utf-8'`。 |
| `python -c` 被安全护栏拦 | 本环境下 `python -c` 后接其它命令会被拒绝；把脚本**存成 `.py` 文件再跑**。 |
| 行号会漂移 | 用**文本锚点**（唯一子串）定位替换，别依赖旧行号。 |
| 同一批多文件编辑 | 一个编辑失败会导致该批后续编辑被跳过；锚点不确定时先 `read` 出确切文本，或把不确定的编辑放到本批最后。 |
| 在 `src/` 里写了中文/全角字符 | 违反第 4 节的硬性规则；`src/` 注释一律英文 ASCII。改完 `src/` 后跑 `bash scripts/check-non-ascii.sh`，确认 `Total non-ASCII lines: 0`。 |
| `git add docs/zh/reference` 带入 `.obsidian/` | 该目录下有未跟踪的 Obsidian 配置（`app.json`/`appearance.json`/`core-plugins.json`/`workspace.json`），会被一并提交。提交前 `git status --short` 复核，误入则 `git rm -r --cached` + `git commit --amend --no-edit`（`--cached` 不会删磁盘文件）。 |
| `$env:TEMP` 与 `%TEMP%` 不是同一个目录 | DSH 沙箱把 `$env:TEMP` 指到私有临时目录，`write` 工具写的 `%TEMP%\drift.py` 在那里找不到；用绝对路径调用。 |
| 把“代码残留”当成“源码怪癖”写进文档 | 例：`Command::getCommandListInfo()` 里 `$phase` 读了不用（重构遗留）。**先判断是不是能清理的残留**：能清就清源码 + 不改文档；确实是刻意为之的行为（如 `Root($switch_phase)` 内部硬编码 `App::Phase`）才写进「注意事项」，并注明“以源码为准”。 |
| 在 Windows 侧直接跑 `phpunit` | Windows PHP 没有 `redis` 扩展，`RedisCacheTest`/`RedisManagerTest` 会报 `Class 'Redis' not found` 的**环境假失败**。测试与 `scripts/*.sh` 一律走 WSL（见第 5 节末），并先 `$env:WSL_UTF8=1` 免乱码。 |

## 8. 当前状态与待办

- **已完成**
  - 110 篇逐类文档全部按本指南规范重写；`drift` 全量扫描 **0 不一致**。
  - 一次“`ref-lastdoc` → HEAD”的增量同步：
    - **新增 8 篇**：`GlobalAdmin-AdminLoginActionInterface.md`、`GlobalAdmin-AdminLoginServiceInterface.md`、`GlobalAdmin-AdminSessionInterface.md`、`GlobalAdmin-AdminSessionTrait.md`、`GlobalUser-UserLoginActionInterface.md`、`GlobalUser-UserLoginServiceInterface.md`、`GlobalUser-UserSessionInterface.md`、`GlobalUser-UserSessionTrait.md`；
    - **更新 20 篇**：GlobalAdmin/GlobalUser 主文档（接口、事件常量、新选项、`login/logout/register`、会话优先、`_Show` 细节），`UserActionInterface`（`urlForRegist`→`urlForRegister`），`UserException`（改继承 `\Exception`），`Core-CoreHelper`（`ChildCall/ProjectThrowOn`、`exception_map`），`Core-Route`（`runFinallyHooks`→`clear`），`Core-App`（`setting`/`exception_map` 选项），`Core-KernelTrait`（finally 改调 `Route::clear()`），`DuckPhp`（`use_user_view/use_admin_view` 改为手动开启），`DuckPhpAllInOne` 与 `Foundation-Helper`（insteadof 清单），`Component-RouteLister`、`Component-RouteHookResource`、`Core-View`、`Helper-App/Business/ControllerHelperTrait`。
  - 后续把 7 篇旧式排版（`Component-DbManager`/`Pager`/`RouteHookPathInfoCompat`/`RouteHookRewrite`/`RouteHookRouteMap`、`Core-SuperGlobal`/`SystemWrapper`）与 4 篇合并表格行（`Ext-CallableView`、`Ext-RouteHookWebInstaller`、`GlobalAdmin`、`GlobalUser`）统一为规范格式——`drift` 因此从 11 处假报收敛到 0。
  - **最近一轮“`doced`（= `3ece976b`）→ HEAD”的增量同步**（3 个提交 `f9160a03`/`2e5340b4`/`fb1bdcf2`）：`git diff --name-only doced HEAD -- src` 只有 6 个文件，其中 3 个是**纯格式化**（`Component/RouteLister.php` 的 `rtrim($path,'/\\')` 加空格、`Core/App.php` 的 `'setting'=>[]`→`'setting' => []`、`GlobalAdmin/GlobalAdmin.php` 的 `getLoginBusiness()` 里 `user_callback_for_login_service`→`admin_callback_for_login_service`——**文档早已写作 `admin_callback_for_login_service`，这次是源码向文档对齐**），因此只需改 2 篇：
    - `Component-Command.md`：命令收集钩子 `getCommandsOfThis($method_prefix, $phase)` → **`__consoleCommands()`**（无参、内部固定 `command_` 前缀）；`getCommandsByClasses(array $classes)`、`getCommandsByClass(string $class, string $method_prefix)` 均**去掉 `$phase` 形参**；新增「注意事项」两条（钩子接管规则；值形态是**对上游 `Console` 执行侧的防御性对齐**，不是本类自创语义）。
    - **顺带清掉源码残留 + 补防御缺口**（同一次重构的遗留，见提交 `96cfbd56`、`15f6cff6` 之后的补丁）：
      - 删掉 `Command::getCommandListInfo()` 里 `$phase = Console::_()->options['console_command_phase'][$namespace]`——`fb1bdcf2` 重构后已成死代码（读了不用）。**文档不该把这类残留当“怪癖”记下来，能清就清源码**。
      - `getCommandsByClasses()` 的取值防御与上游 `Console` 对齐：原先只判 `=== false`，而 `Console` 判的是 `!isset($method_prefix) || $method_prefix === false`；本文件是 `declare(strict_types=1)`，故 `cmd` 里给某类配 `null` 前缀（`Console` 会跳过）时这里会抛 `TypeError`。补 `!isset(...)` 后两边取法逐条一致。
      - 补回归测试 `tests/Component/CommandTest.php`：`getCommandsByClasses()` 覆盖 `true` / `false` / `null` / 字符串四种取值形态。修复前该测试**确实复现** `TypeError: Argument 2 passed to DuckPhp\Component\Command::getCommandsByClass() must be of the type string, null given`，修复后通过。
      - 另清掉两处无害残留：`getCommandsByClasses()` 上方重复且失效的空 docblock（三行一样的 `@param array<string, mixed> $classes`）、`getCommandListInfo()` 里 `//::{$v['class']}` 注释。
      - 教训：**文档里的“怪癖”要先分清是「能清的代码残留」还是「刻意行为」**——前者清源码（并顺手补测试），后者才写进「注意事项」。
      - 校验（全部在 WSL 下）：`php vendor/bin/phpunit --no-coverage tests/Component/CommandTest.php` → `OK (1 test, 14 assertions)`；`bash scripts/check-non-ascii.sh` → `Total non-ASCII lines: 0`；`drift.py doced` 仍全 `ok`。
    - `Core-KernelTrait.md`：`Root()` → **`Root($switch_phase = false)`**（为真时顺带 `App::Phase(根 Phase)`，用于“子应用里取根实例并切回根”）；补第 7 条注意事项（返回实例本身不改当前 Phase；切阶段那步硬编码在 `App` 上）。
    - 收尾：`drift.py doced` 与 `drift.py --all` 均 **0 `missing-*`/`extra-option`**（仅剩 12 处示例方法 `extra-method`，属正常）。
- **待办（本工作范围外）**：
  - `docs/zh/reference/index.md` 目录页与各篇文件名/说明的核对（含上面 8 篇新文档尚未登记进目录页）；
  - `options.md` / `options-by-class.md` / `options-index.md` 三个汇总页（内容过时且行文损坏，建议改为由脚本生成）；
  - `docs/zh/guide/` 教程与 reference 的交叉引用校对。
- **生成器已知缺陷（如需修复）**：`scripts/gen-reference.php verify` 对含 trait 别名 override 的大文件（`Core/App.php`）会漏列方法；修好前请以 `drift.py` 为准。

## 9. 快速自检（冒烟）

```powershell
$env:WSL_UTF8=1   # 一次即可，避免 wsl 输出乱码

# 1) 全量漂移：改完文档后应无 missing-*（extra 只会是示例方法）
python %TEMP%\drift.py --all

# 2) 编码抽查：把下面脚本存成 %TEMP%\enc.py 后运行
python %TEMP%\enc.py

# 3) 本次改了 src/ 或 tests/ 时（一律走 WSL，按单个测试文件跑，见第 5 节末）：
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Component/CommandTest.php"
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && bash scripts/check-non-ascii.sh"   # 期望 Total non-ASCII lines: 0
```

```python
# %TEMP%\enc.py —— 检查 reference 下每篇 md 是否为 UTF-8（报 non-utf8 即为 GBK，需重写）
import glob
bad = []
for p in glob.glob('docs/zh/reference/*.md'):
    try:
        open(p, 'rb').read().decode('utf-8')
    except UnicodeDecodeError:
        bad.append(p)
print('non-utf8:', bad if bad else 'none')
```

> 提示：文档同步基线有两个——历史 tag `ref-lastdoc`（更早那轮），以及分支 **`doced`（= `3ece976b`，2026 那轮“文档更新结束”）**，后者是**最近一次同步的基线**。做下一次同步时把命令中的 `<tag>` 换成当前基线；同步完成后，用当时的 HEAD 更新这里的记录（如 `git branch -f doced HEAD` 或新开标签）。

## 10. 扫描器报 missing 时怎么判读（判假报 vs 真缺）

`drift.py` 报 `missing-*` 时，先分清是**假报**还是**真缺**：

**常见假报（文档其实有，只是形式让脚本读不到）**

- 选项表把多个键写在**同一行**（`` | `a` / `b` | ``）：脚本只按“一行一键”匹配 → 拆成一行一键即可。
- 方法条目**缺 `function` 或可见性词**（如 `    static PageNo(...)`），或把静态壳与实例实现**合并成一行**（如 `DbForWrite() / _DbForWrite()`）：脚本按「可见性 + `function`」匹配 → 按第 3 节拆成规范条目。
- 键名含连字符（如 `psr-4`）：早期脚本会误判，**当前脚本已支持**。

**判定为真缺（必须补）**：以上形式都正常、且该名字在源码里确实存在时，就是文档少写了条目/选项。

**历史记录（此前的假报已全部清零）**：脚本首次全量扫描报出 15 处，其中 2 处是真缺（`Component-Configer.md` 缺 `path` 选项 + 整个「全部选项」节、`Core-Logger.md` 缺 `path` 表格行），另外 13 处即上述两类形式假报。这些**现已全部修复**（统一格式 + 补齐真缺），`drift.py --all` 现输出 **0 假报**。
