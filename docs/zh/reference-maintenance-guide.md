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
  - 检查命令：`bash docs/scripts/check-non-ascii.sh`（等价于 `grep -rnP '[^\x00-\x7F]' src/ --include='*.php'`）。**必须在 WSL 下跑**（Windows 侧没有 bash）。
  - 判定：输出 **`Total non-ASCII lines: 0`** 才算通过——该脚本**不设置失败退出码**，不要只看 `$LASTEXITCODE`。
  - 命中后：把命中行改写成英文 ASCII 注释，再重跑确认清零。
- **不虚构**：源码没写的机制不要编；源码里的“怪癖/不一致”要如实在“注意事项”里说明，并注明意图（如 `RouteLister::listAll()` 的 `only_admin/only_user` 会强制打开 `only_controller`、`PermissionMenu` 里未加注释的方法名带 `action_` 前缀）。**但先分清是「能清的代码残留/笔误」还是「刻意行为」**——前者改源码（并顺手补测试），后者才写进文档；判断不了就去问作者一句，别替源码下结论。

## 5. 工具

> **所有工具脚本都在 `docs/scripts/` 下**（不是 `scripts/`）：它们只服务文档生成与校验，**跟文档一起提交**；命令一律从仓库根目录执行。以后新增文档工具也放这里。

| 工具 | 用途 |
|---|---|
| `docs/scripts/gen-reference.php` | `facts <src-rel>` 打印解析结果；`skeleton [--out DIR] [--file REL]` 生成骨架；`verify --file <md>` 比对方法/选项 |
| `docs/scripts/gen-route.php` | 极简版骨架生成（Route 风格，只抓声明/方法行/options 原文） |
| `docs/scripts/find-unmentioned-classes.py` | **反查孤儿页**：扫 `docs/zh/guide/*.md` 里指向 `../reference/*.md` 的链接，报「指南从没链到」的类页（`--all` 另列只链 1 次的；纯链接判定，正文写了类名但没挂链接不算） |
| `docs/scripts/covagg.php` | **汇总全量覆盖率**：遍历 `test_coveragedumps/` 的每个 dump，按源文件合并命中（xdebug3 的数组型命中值取并集），打印还有未执行行的文件与总 `lines x/y`；`--quiet-ok` 只报缺口。判全量覆盖率用它或 `test_reports/index.html`，别只看单个类的 dump |
| `docs/scripts/check-md-layout.py` | **判「这次改动是不是只有排版」**：把工作区与 `HEAD` 逐文件比对，归一化空白 / 表格补位 / 多余空单元格后仍相同就报 `layout-only`（可以放心 `git checkout --` 丢掉），真改了内容才报 `CONTENT`。默认只看 `git status` 里改动的 md，`--all` 看全部，也可显式给文件路径 |

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

**判读**：`missing-method` / `missing-option` / `extra-option` 非空 = 文档确实缺内容或写多了选项，必须处理。`extra-method` 一般是「使用方式」示例里自定义的方法，**但不一定**——它同样可能是**真过期条目**（文档还写着源码里已经没有的方法）。实测踩到过：`Core-ComponentBase.md` 里留着 `IsAbsPath()`/`SlashDir()` 两个条目，而这两个方法早已从 `ComponentBase` 移到 `App`（改名 `isAbsPath()`/`slashDir()`）与 `AutoLoader`，`extra-method` 正是唯一线索。**每次都要扫一眼 extras**，别一律当示例忽略。同一键合并成一行（如 `| \`a\` / \`b\` |`）会被 doc_opts 漏读 → 假报 `missing-option`，拆成单键行即可。

> ⚠️ **本会话环境注意**：DSH 沙箱里 `$env:TEMP` 指向私有临时目录，与 `write` 工具写的 `%TEMP%` 不是同一个；跑脚本请给**绝对路径**（如 `python "C:\Users\<你>\AppData\Local\Temp\drift.py" doced`），否则报 `No such file or directory`。

### 运行环境：测试与 `bash` 脚本走 WSL（硬性）

**PHPUnit 与 `docs/scripts/*.sh` 一律在 WSL 下执行**，不要在 Windows 侧直接跑 `php vendor/bin/phpunit`。

**默认只跑与改动相关的「单个测试文件」**——`phpunit.xml` 开了 `processIsolation="true"`，跑一个目录或全量都很慢（实测单文件约 2 秒，`tests/Component` 整目录约 36 秒，全量更久），没必要不要跑。

```powershell
# 建议先设一次，避免 wsl.exe 输出 UTF-16 乱码（否则中文/结果全成方块）
$env:WSL_UTF8=1

# 改哪个类就测哪个类的测试文件（秒级）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Component/CommandTest.php"

# 需要时按方法名再收窄
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage --filter testAll tests/Component/CommandTest.php"

# 硬性规则脚本
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && bash docs/scripts/check-non-ascii.sh"

# 只在「大改 / 要交差」时才跑目录或全量（很慢，建议放后台）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Component"
```

- 测试文件与被测类的对应：`src/A/B.php` → `tests/A/BTest.php`（如 `src/Component/Command.php` → `tests/Component/CommandTest.php`）。
- 本机环境实测：WSL（Debian）**PHP 8.2.3, NTS**，`php -m` 含 **redis** 扩展；Windows 侧 PHP **没有 redis 扩展**，于是 `RedisCacheTest` / `RedisManagerTest` 会报 `Error: Class 'Redis' not found`——**那是环境假失败，不是代码问题**（WSL 下 `tests/Component` 实测 `OK (17 tests, 123 assertions)`）。
- 项目路径在 WSL 下是 `/mnt/e/ProjectGoat/DNMVCS`。
- `drift.py` 也可在 WSL 下用 `python3` 跑（如 `wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 /mnt/c/Users/<你>/AppData/Local/Temp/drift.py doced"`），结果与 Windows 侧一致。

### 看覆盖率：`test_coveragedumps/` 里那个类的 dump（硬性：必须带 `XDEBUG_MODE=coverage`）

写完 / 改完测试后，用覆盖率确认“这个类的可执行行都跑到了”：

```powershell
$env:WSL_UTF8=1
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && XDEBUG_MODE=coverage php vendor/bin/phpunit tests/Ext/PermissionMenuTest.php"
```

- **必须给 `XDEBUG_MODE=coverage`**：不给的话 dump 里每一行的命中数都是 0，看起来像“一行都没跑到”。
- dump 的文件名就是类路径：`test_coveragedumps/Ext/PermissionMenu.php`（该目录已被 `.gitignore`，不要提交）。
- ⚠️ **不要同时给 PHPUnit 的 `--coverage-php/clover/html/text/crap4j`**：`LibCoverage::isSkip()` 一看到这些参数就**跳过自己的 dump**——该类的 dump 文件根本不会生成（看起来像“这个类没被测/没覆盖”）。查 LibCoverage 覆盖率只能用 `XDEBUG_MODE=coverage php vendor/bin/phpunit`。2026-09-22 实测踩过：加 `--coverage-php /dev/null` 后 `ModelHelperTrait.php` 的 dump 消失，去掉就回来了。
- 薄壳类（只有 `class X extends Y {}` 或 `abstract class X { use A; use B; }`）的 dump **本来就可能是空的**：它们自己的文件没有可执行行，方法体算在 trait / 父类的文件里——这不是漏测。**接口文件更是根本不进 dump**（`interface X { public function f(); }` 没有可执行行）：当前 `src/` 109 个 php 文件里有 19 个属于这种情况（18 个接口 + `Core/DuckPhpSystemException.php`，后者的类体只剩注释）。要确认「没漏测」就按「有可执行行的文件都 100%」来判定，别按文件数对齐。
- dump 是序列化的 `CodeCoverage` 大对象、含二进制字节：**别 `cat`/`head`/`grep` 它**（刷屏、乱码，`grep` 只会回一句 `binary file matches`）。用脚本只打印“没执行的行”：

```python
# cov.py —— 读 dump 并打印未执行的可执行行
import re, sys

txt = open(sys.argv[1], encoding='utf-8', errors='replace').read()
m = re.search(r'lineCoverage";a:\d+:\{s:\d+:"([^"]+)";a:\d+:\{(.*?)\}\}s:74:', txt, re.S)
if not m:
    print('cannot parse dump: %s' % sys.argv[1]); sys.exit(1)
src_file, body = m.group(1), m.group(2)
hits = {int(l): int(c) for l, c in re.findall(r'i:(\d+);a:(\d+):', body)}
uncovered = sorted(l for l, c in hits.items() if c == 0)
print('%s' % src_file)
print('lines: %d/%d covered, %d not executed' % (len(hits) - len(uncovered), len(hits), len(uncovered)))
lines = open(src_file, encoding='utf-8', errors='replace').read().splitlines()
for l in uncovered:
    print('  %4d | %s' % (l, lines[l - 1].strip()))
```

```powershell
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 /mnt/c/Users/<你>/AppData/Local/Temp/cov.py test_coveragedumps/Ext/PermissionMenu.php"
```

- 判读：`0 not executed` 才算 100%。剩下的通常只有**不可达的防御代码**（典型：前面已 `class_exists()` 判过、`new \ReflectionClass()` 不可能抛的 `catch`）。**不要为了凑覆盖率写假调用**：要么留着并在第 8 节记一句，要么给源码那两行加 `@codeCoverageIgnore`。
- 要看逐行标红的 HTML 报告：`wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && XDEBUG_MODE=coverage php vendor/bin/phpunit tests/support.php"` → `test_reports/<路径>.php.html`（等于全量跑一遍，慢，非必要不用）。

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
   - **必须再跑** `bash docs/scripts/check-non-ascii.sh` 并确认输出 `Total non-ASCII lines: 0`（见第 4 节的硬性规则）。
4. **登记**：更新第 8 节的「现状」——**一两句结论**即可（本次同步了哪些、校验结果如何）。**不要把逐文件清单、源码修复过程、调试经过堆进第 8 节**：那些属于 commit message 与对应文档，第 8 节只回答「现在什么状态」和「还欠什么」，保持它短到能一次看完。有长期价值的**教训**请写进第 7 节的陷阱表。
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
| Trait 转发类 | 如 `Foundation\Model\ModelHelper`（`use Model\ModelHelperTrait`）不重复列方法，只说明“方法全部由某 trait 提供”，并链到该 trait 文档（`Foundation-Model-ModelHelperTrait.md`）。 |
| GBK 旧文件 | 读取报 `0xbc` 即 GBK，需以 UTF-8 重写整篇。 |
| PowerShell 写中文 | 禁止 `Set-Content`/`Out-File` 直接写中文（会乱码）；用编辑器/`write_file` 工具写 UTF-8，或 `python` 显式 `encoding='utf-8'`。 |
| `python -c` 被安全护栏拦 | 本环境下 `python -c` 后接其它命令会被拒绝；把脚本**存成 `.py` 文件再跑**。 |
| 行号会漂移 | 用**文本锚点**（唯一子串）定位替换，别依赖旧行号。 |
| 同一批多文件编辑 | 一个编辑失败会导致该批后续编辑被跳过；锚点不确定时先 `read` 出确切文本，或把不确定的编辑放到本批最后。 |
| 在 `src/` 里写了中文/全角字符 | 违反第 4 节的硬性规则；`src/` 注释一律英文 ASCII。改完 `src/` 后跑 `bash docs/scripts/check-non-ascii.sh`，确认 `Total non-ASCII lines: 0`。最常见的来源是从中文文档里**复制粘贴**：全角箭头 `→`（`PermissionMenu.php` 就带进来 4 行）、全角括号/逗号/空格。 |
| 源码文件里接口名与文件名不一致 | 如 `CommandMetaInterface.php` 里曾声明成 `interface CommandDescInterface`。按**文件名 + 实际用法**（方法名 `__commandMeta()`、调用处）判断哪个是笔误，改源码统一，再同步文档 H1／交叉引用／`index.md`；**不要在文档里长期挂一条“命名不一致”的告警**当挡箭牌。 |
| 给不可达代码加 `@codeCoverageIgnore` | php-code-coverage 只认**整条注释恰好等于** `// @codeCoverageIgnore`，而且**忽略的是注释所在行**——所以要贴在 `catch (...)` 行与 `return` 行**各自的行尾**（本仓库 `Core/Logger.php`、`Core/App.php` 就是这么写的），写成「注释单独占上一行」无效；要忽略一段区间才用 `// @codeCoverageIgnoreStart` / `End`。 |
| 回归测试没验证过“抓得住 bug” | 写完断言后，**把 bug 临时改回去跑一遍**，确认断言真的失败（顺手把失败输出贴进记录），再还原源码。没做这步的回归测试等于没写——`GlobalUserTest` 那次就是这么证明的。 |
| `git add docs/zh/reference` 带入 `.obsidian/` | 该目录下有未跟踪的 Obsidian 配置（`app.json`/`appearance.json`/`core-plugins.json`/`workspace.json`），会被一并提交。提交前 `git status --short` 复核，误入则 `git rm -r --cached` + `git commit --amend --no-edit`（`--cached` 不会删磁盘文件）。 |
| **Obsidian 保存时重排表格 / 别的编辑器或 AI 会话顺手格式化** | 表现：表格被按显示宽度补齐、`\|---\|` 变成 `\| --- \|`、**多出空列或纯空表格行**（实测 `options-index.md` 一个 4 列表被扩成 7 列；2026-09-25 又实测 `docs/zh/guide/{index,controllers,views,user,security-performance}.md` 被这样改过）。后果：整页生成的 `options-index.md` / `options-by-class.md` 被 `gen-options-docs.php --check` 判成 stale，生成器一跑又把排版冲掉，来回打架；手写章则把纯排版噪声混进提交。**先查是谁在改**：`docs/zh/.obsidian/` 是当前唯一在用的库（`workspace.json` 有当天 mtime），它**没装任何 community 插件**（无 `plugins/` 目录、无 `community-plugins.json`），所以「Advanced Tables 的 format-on-save」在这台机器上**已经不是**元凶；三个 `*.obsidian/` 里的 `community-plugins.json` 已显式写成 `[]` 把插件关死。**再定性**：跑 `python3 docs/scripts/check-md-layout.py`，报 `layout-only` 的直接 `git checkout -- <file>` 丢掉（或对生成页别放进 Obsidian）；报 `CONTENT` 才需要看 diff。生成页的对拍/写入另有 `normalize_layout()` 兜底（忽略纯对齐差异，输出里提示 `layout-only differences ignored (N)`）——但**空列/空行属实质差异**，按 HEAD 的列数砍掉即可，不必整页重生。 |
| `$env:TEMP` 与 `%TEMP%` 不是同一个目录 | DSH 沙箱把 `$env:TEMP` 指到私有临时目录，`write` 工具写的 `%TEMP%\drift.py` 在那里找不到；用绝对路径调用。 |
| 把“代码残留”当成“源码怪癖”写进文档 | 例：`Command::getCommandListInfo()` 里 `$phase` 读了不用（重构遗留）。**先判断是不是能清理的残留**：能清就清源码 + 不改文档；确实是刻意为之的行为（如 `Root($switch_phase)` 内部硬编码 `App::Phase`）才写进「注意事项」，并注明“以源码为准”。 |
| 在 Windows 侧直接跑 `phpunit` | Windows PHP 没有 `redis` 扩展，`RedisCacheTest`/`RedisManagerTest` 会报 `Class 'Redis' not found` 的**环境假失败**。测试与 `docs/scripts/*.sh` 一律走 WSL（见第 5 节末），并先 `$env:WSL_UTF8=1` 免乱码。 |
| 改公共名字（方法名／选项键／参数名）没改干净 | 一次性覆盖 `src/` + `tests/` + `docs/zh/reference/` + `docs/zh/guide/`：先 `grep -rn "<旧名>" src tests docs` 列全，改完再 grep 残留 = 0。别漏调用处（`Ext/SqlDumper` 在调 `Db::quoteInsertArray()`）。**产物不用动**：`docker/test-php84/test_reports/`、`test_coveragedumps/`、`tests/data_for_tests/*.txt` 都是跑测试生成的。另外文档里可能故意保留「由旧名 X 更名」的历史说明，`sed` 批量替换时要先排除这类句子。 |
| `ZAllDemoTest` 报 `Failed: <路由> => A(B)` | 该用例把 demo 各路由的**输出字节长度**跟 `tests/data_for_tests/ZAllDemoTest.config.php` 里的期望值硬比，而 `files` 路由会 dump App 的**选项表**（「应用的选项」「全部选项」两个 fieldset，含 `合计 N个`）、方法表、包含文件表与**调用栈行号**——**源码一动（加/删方法或选项、行号漂移、`$options` 与 `$hidden_options` 之间搬家）长度就变**。改完 `src/` 后若只有它红：把 config 里的期望值改成括号里的 B 即可（实际内容同时被写到 `tests/data_for_tests/ZAllDemoTest-<长度>.txt`，可直接 `diff` 新旧两份 dump 看差在哪，尾部的 `执行耗时/内存消耗` 数字位数也会让长度抖 ±1）。**别先怀疑自己的改动**——先确认自己没碰 `src/`，再 `git stash push -- src` 跑一遍确认是否本来就在红；跟别的会话并行改同一个工作区时，这个数字会互相打架（当前基线见第 8/9 节）。 |
| 旧指南里的 API／示例可能早就失效 | 实测：`docs/zh/guide/advanced-phase.md` 里 3 处 `App::Root()->getOverridingClass()`（源码里**没有**这个方法）；`helper.md` 的 `assignRewrite('article/123', …)` 少了前导 `/`，钩子内部拿 `'/'.$path_info` 比较 → 永不命中；`Configer` 读的是 `config/<名>.php`（不是 `<名>.config.php`）。**改写旧章前先核对源码，别原样搬旧示例**。 |
| 指南里的示例没实跑过 | 本仓约定：新指南的示例必须能跑。第三卷（3-1–3-7 章）全部挂在 `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php`（36 断言）上，改示例就重跑它；第一/二卷的兜底是 `demo/`（`tests/ZAllDemoTest.php` 起内置服务器跑它）与 `skeleton/`（脚手架骨架）。**别引用 `tests/data_for_tests/ZAllDemo`——那个目录从未存在**（见第 18 条）。 |
| **正文里写的仓库路径是假的** | 死链检查只查 `.md` 之间的链接，**查不出「正文里引用的源码/测试路径不存在」**——它不报错，只骗读者。实测：`tests/data_for_tests/ZAllDemo` 被指南引用了 25 处（五层骨架、示例应用、dump 页……），而**这个目录从未进过 git、磁盘上也没有**（是写章节时按测试名 `ZAllDemoTest` 拼出来的）；同类还有 `layers.md` 的 `ZAllDemo/src/Controller/Helper.php`。**规矩：引用路径前先 `Test-Path`（或 `git ls-files`）验证一次**；批量核查看正文里的反引号路径（`grep -o '`[a-z][^`]*\.php`'`）。见第 18 条。 |
| 多应用相关的斜杠坑（写示例时必踩） | ① `RouteHookRewrite::assignRewrite()` 的**键必须带前导 `/`**；② `controller_resource_prefix`：根应用写 `'/res/'`、子应用写 `'res/'`（前缀按 `'/'.controller_url_prefix.controller_resource_prefix` 拼，子应用的挂载前缀已带尾斜杠，再带前导斜杠就变成 `//`）；③ 相位名不是类名（子应用是 `:<name>`，不是 `\X\System\App`）。 |

## 8. 当前状态与待办

- **现状**（每轮只更新这一段；历轮细节看 `git log` 与各篇文档，**不要在这里堆流水账**）
  - `docs/zh/reference/` 共 **114 篇**：110 篇逐类文档 + 4 个汇总页（`index.md`、`options.md`、`options-by-class.md`、`options-index.md`）。逐类文档全部按第 3 节模板；`drift.py --all` **0 不一致**（`missing-*` / `extra-option` 全空，`extra-method` 只剩示例代码里的自定义方法）。
  - `enc` 检查 114 篇全 UTF-8；站内链接 0 死链。同步基线见第 9 节末的提示（最近一次基线是分支 `doced`，下次同步从 `66a93720` 之后算起）。
  - 测试基线（WSL）：全量 `php vendor/bin/phpunit` → `OK (96 tests, 823 assertions)`；`XDEBUG_MODE=coverage` 跑完看 `test_reports/index.html` → **`Lines 4895/4895 (100.00%)`**（Functions/Methods 与 Classes/Traits 同为 100%）。`tests/data_for_tests/ZAllDemoTest.config.php` 里 `files` 的期望长度是 **10438**（跟当前工作区 `src/` 的选项表绑定，见第 7 节那一行）。
  - 最近几轮（每轮一句话，细节在 commit message 与对应文档里）：
    1. `doced` → HEAD 增量同步：`Component-Command`（命令收集钩子改名、两个收集方法去掉 `$phase` 形参）、`Core-KernelTrait`（`Root($switch_phase = false)`）。
    2. 新类 `Ext/PermissionMenu`：补齐测试 `tests/Ext/PermissionMenuTest.php`（覆盖率 298/298），修掉测试暴露的 6 个源码问题（3 个真 bug、2 处语义调整、1 处不可达 `catch` 加 `@codeCoverageIgnore`）。
    3. 参考文档按 `drift.py --all` 归零：**新增 3 篇**（`Ext-PermissionMenu`、`Ext-PermissionMenuMetaInterface`、`Component-CommandMetaInterface`），**更新 7 篇**（`Component-Command`、`Core-App`、`Core-AutoLoader`、`Core-ComponentBase`、`Core-Logger`、`DuckPhp`、`GlobalAdmin`/`GlobalUser`），`index.md` 补登 11 条。
    4. `GlobalUser::id()/name()` 读错了选项键（读成 `admin_default_exception_class`，白名单里没有 ⇒ 死选项）→ 改为 `user_default_exception_class`；`tests/GlobalUser/GlobalUserTest.php` 加了回归测试。
    5. **清怪癖一轮**（作者裁定，见第 7 节新增的陷阱行）：修拼写 `$contetxt`→`$context`、`qoute*`→`quote*`、`user_callback_for_url_for_regist`→`..._register`；`AdminException` 改为 `extends \Exception`（并在 `Core-DuckPhpSystemException` 文档里立下「只用于框架内部系统错误，外部异常别继承」的规矩）；`Db::execute()` 的 rowCount 语义修正（成功=受影响行数、失败=0）；`HttpServer::init()` 补置 `is_inited`；`App::getOverrideableFile()/getConfigFile()` 新增 `$must_exist` 区分「读文件要存在」与「写入要一个尚不存在的候选路径」，不存在时返回 `null` 而不是空串。
       - 顺带修掉一处**本就红的**用例：`ZAllDemoTest` 的 `files` 期望长度 10347 是过期的（`App::isAbsPath/slashDir` 加进方法表后应为 10429），已更新 `tests/data_for_tests/ZAllDemoTest.config.php`（详见第 7 节那一行）。
    6. `PermissionMenu` 复查（作者逐条裁定）：**特性**照旧但文档改写成「刻意行为」的语气——未注释方法名带 `action_` 前缀（便于反查代码）、权限点不带图标（权限标记非导航项）、`permissionMenuTreeToSideMenuTree()` 只留可导航节点、同目录多条 `@menu_directory_url` 只用第一条；**新变更**：控制器没写 `@menu_directory` 时目录名改用**类名 basename**（新增 `getDefaultDirectoryName()`，只有类名为空才回落 `NoName`）；**修 bug**：`resolveUrls()` 不再把 `url: null` 的节点变成光秃秃的前缀（`'/admin/'`），而是保持没有 url。
    7. 事件常量拼写收尾：`BusinessHelperTrait::$EVENT_REGISTING/REGISTED` 与 `ControllerHelperTrait::$EVENT_ACTION_REGISTING/REGISTED` → **`REGISTERING/REGISTERED`**（值同改 `registering`/`registered`、`action_registering`/`action_registered`）。全仓 grep 确认这两个常量**只有定义、没有任何派发/监听点**，所以改值无内部影响（工程侧监听旧名需自行跟进，已在两篇文档的注意事项里注明）。登录/登出侧 `LOGINING/LOGINED/LOGOUTING/LOGOUTED` 保持原样——那是与 `GlobalUser`/`GlobalAdmin` 常量一致的框架既有写法。
    8. **用户指南重写启动，第三卷（使用第三方应用）已落稿**：新增示例工程 `tests/data_for_tests/ZThirdDemo`（主应用 + `third/` 里被挂的第三方应用，含视图/配置/资源/控制器四类覆盖、跨相位调用、事件总线、安装流程）与 `tests/ZThirdDemoTest.php`（36 断言；全量 **92 tests / 556 assertions** 全绿）；指南侧：一页总目录 `docs/zh/guide/index.md`、附录 A 术语表 `appendix-glossary.md`、改写第 3-1 章 `advanced-phase.md`、新写 3-2–3-7 章（`mount-app`/`static-resources`/`component-sharing`/`overriding`/`installer`/`case-multi-app`，均 ≤400 行）；`docs/zh/index.md` 瘦身为指路页。**下一轮（M2）是第二卷 1-2–1-1–2-17 章的改写与补齐**（其中原第 2-11 章「中间件与钩子链」已并入第 2-10 章），计划与开放问题见对话记录。
    9. **第一卷（入门 1-1–1-7 章）落稿**：`intro.md`（新写，含与 Laravel/Yii2/CI 的定位对比）、`project-structure.md`（新写）、`install.md`/`quickstart.md`/`configuration.md`/`debugging.md`/`deployment.md`（改写），总目录 `docs/zh/guide/index.md` 第 1–7 行接上；全部示例挂在现成的 `demo/` 上，行数 101–226（≤400）。校验：`docs/zh` 站内链接 **1005 条 0 死链**（`docs/old/`、`docs/en/` 的历史死链不算），`docs/zh` 无非法 UTF-8 文件。顺手修掉 `demo/public/helloworld.php` 里 `catch (\Thowable $e)` 的拼写（旧文档抄进示例的错字；`catch` 子句里的类名不会触发自动加载，所以这个 `catch` **永远不命中**，真出异常时直接穿透——示例想演示的「静默兜住」根本没生效）。
    10. 测试基线重新对齐：`ZAllDemoTest` 的 `files` 期望长度 10429 → **10360**。原因不是文档改动，而是并行会话的**选项表重构**：`src/DuckPhp.php` 把 `session_prefix`/`table_prefix`/`duckcoverage_test_lister` 从 `$common_options` 移进新增的 `$hidden_options`（文档用元数据，注释明确写了**不参与 `$options` 合并**），`src/Core/App.php` 则把 `skip_404` 从「读了没声明」提升为正式选项 ⇒ `files` 页的选项 dump 由 53 个键变 51 个，加上 `App.php`/`DuckPhp.php` 行号漂移，长度正好差 69 字节（`diff` 两份 dump 可见差异只落在「执行时间」「应用的选项」「调用堆栈行号」三处）。**已核实不是回归**：`KernelTrait::initOptions()` 是无过滤的 `array_replace_recursive($this->options, $options)`（`ComponentBase::initOptions()` 只是空钩子， trait 方法胜出），所以用户传的这些键仍会进 `$options`，只是不再有默认值占位（各读取点都带 `?? ''`）。
    11. **第二卷（单一应用 2-1–2-17 章）落稿**：17 章全部 ≤400 行（123–293），新写 6 篇（`controllers`/`views`/`events`/`cache`/`i18n`/`security-performance`）、改写 11 篇；`layers.md` 由 581 行拆薄为「第 2-1 章 四层架构与调用规范」（226 行），控制器/视图内容独立成第 10、11 章；原第 2-11 章「中间件与钩子链」按作者裁定并入第 2-10 章（中间件作为兼容性扩展，并如实写出「短路无效」的坑）。校验：`docs/zh` 站内链接 **1281 条 0 死链**、全量 `OK (92 tests, 556 assertions)`；示例全部指向现成 `demo/`、`ZAllDemo`、`ZThirdDemo`（**M16 更正**：`ZAllDemo` 这个目录从未存在，相关引用已在第 18 条里全部改指 `skeleton/` 与 `demo/`），并实测了 `php demo/cli.php help|routes|DbTestApp:version`。**下一轮（M4）是第四卷 1-4–1-1–4-10 章 + 附录 B/C/D**，详见 `guide-maintenance-guide.md` 第 7–10 节与 `guide-rewrite-checklist.md`。
        - 本轮顺手记下的**遗留失真**（未改源码，见 `guide-maintenance-guide.md` §10 末）：`demo/src/Controller/Commands.php` 引用了不存在的 `Foundation\CommonCommandTrait`；`demo/public/dbtest.php` 的 `cli_command_prefix` 与 `bin/duckphp` 的 `cli_command_classes` 是死选项；`demo/src/Model/CrossModelEx.php`、`demo/src/Business/CommonService.php`、`demo/src/Controller/CommonAction.php` 都是空壳样板（文档已按「占位样板」表述，没有把它们写成现成功能）。
    12. **第四卷（高级话题 4-1–4-10 章）+ 附录 B/C/D 落稿** ⇒ **用户指南全书 41 章 + 4 附录完成**。新增：`container-phases`/`custom-component`/`replace-behavior`/`embed`/`http-server`/`multi-entry`/`coverage`/`doc-maintenance`/`troubleshooting`/`design-notes`（80–206 行）+ `appendix-snippets`/`appendix-migration`/`appendix-faq`。第 32/33 章吸收了 `architecture.md` 与 `components.md`（**注意旧文有错**：它写的公共桶 `@public@` 实为 `#public`，相位名也不是类名而是 `:<name>`，新章按源码写）。校验：`docs/zh` 站内链接 **1613 条 0 死链**、全量 `OK (92 tests, 556 assertions)`。
        - **M5 已于下一轮收尾**：全局函数参考**不新建 `GlobalFunctions.md`**（作者裁定，由现成的 `reference/Core-Functions.md` 承接）；guide 侧 `appendix-global-functions.md`、`appendix-options.md` 与 `architecture.md`、`components.md` 均已删除（内容分别进第 32/33/17/8 章与参考手册的 options 三页），删除后 `docs/zh` 站内链接仍 **1622 条 0 死链**。`docs/en/` 有同名副本，未受影响。
    13. **Q3/Q4 收尾（作者裁定"修复吧"）**：
        - **Q4**：`DuckPhp::_Show()` 里那个没赋值的死表达式其实已在 `9635a77b` 删除（`App::_Show()` 现有正确赋值），本轮**补了防回归断言**（`tests/DuckPhpTest.php`：`calling_path='block'` + `_Show($data,'')` 必须渲染出 `view/block.php`），并实测「把 bug 改回去会红」。
        - **Q3**：修 `skeleton/` 的失真——`agent-zh.md` 的旧选项 `class_user`/`class_admin` → `user_provider`/`admin_provider` + `user_callback_for_*`；`RULES.md` 的方法前缀默认值 `action_` → 空串、`/Main/index` 示例标注 E009；`App.php` 的 `cmd` 注释示例改为 `[Class => true]`。**顺带修掉真 bug**：`skeleton/` 与 `demo/` 的 `ProjectException` 只 `use ExceptionTrait` 而没 `extends \Exception`（抛它必致命错误）→ 两处均改为 `extends \Exception`，并在 `tests/Foundation/ExceptionTraitTest.php` 加回归测试。
        - 测试基线：**`OK (93 tests, 565 assertions)`**（原 `92/556`）；`src/` 未改，`check-non-ascii.sh` 仍为 0。**用户指南重写任务（M0–M5 + Q1–Q4）至此全部完成。**
        - 本轮在 `reference/` 侧无改动；`options.md` / `options-by-class.md` / `options-index.md` 已由作者侧重建，本轮复查 `class_admin`/`class_user`/`FastInstaller` 残留 = 0。
    14. **M6：章号改「卷-章」形式 + 类名首现加参考链接 + 清掉过时内容**（作者三条裁定，只动 `docs/`）：
        - 章号统一成 `1-1` … `4-10`（单数字废弃）：41 章 H1、总目录、checklist 条目、全库 `第 N 章` 与范围写法交叉引用，共约 700 处、49 个文件；
        - **类名第一次出现链接参考手册**：从 `reference/*.md` 的 H1 建「类全名 → 页」表（113 个类页），在指南里给每个类找首现加链，共 **新增 359 处 / 45 个文件**（净增 347；每章至少一条）；`Base`/`Helper` 多页同名 ⇒ 只用全限定名链接；优先链正文裸出现，没有时退回「链整段行内代码」；
        - 指南里删掉 5 处「旧文档里的 X 已失效 / 旧文档遗留」这类表述（旧文已删，只写当前事实），`intro.md` 里把「⏳ 撰写中」当章号用的过时说法也换成了正式链接；
        - **三个踩坑已写进 `guide-maintenance-guide.md` §14**：①「表格首列是数字」的替换规则**不能全库套用**——第一次整库替换把 `setting.md`/`i18n.md` 里「次序 1/2/3」的普通编号表改成了 `1-1/1-2/1-3`，只能 `git checkout` 回滚重做（正确规则：表格行只对总目录与 checklist 生效、H1 只在 `docs/zh/guide/` 内生效）；②**加链接脚本必须跳过围栏代码块**——第一版把 76 处链接插进了 PHP 示例；③**行内代码整段替换的坑**——把 `` `View::getViewFile()` `` 换成 `` [`View`](…) `` 会吞掉 `::getViewFile()`，必须「整段当链接文字」或「另找一处」。
        - 校验：41 章 H1 / 总目录 / 交叉引用 / 无单数字章号 = **0 处不一致**；`docs/zh` 站内链接 **1973 条 0 死链**；代码块内链接 **0 处**；`docs/zh` 全 UTF-8；三条新规矩已写进 `guide-maintenance-guide.md` §1 硬约束（第 6/7/8 条）与 §2 模板约定。
    15. **参考页孤儿清零（本轮）**：新增 `docs/scripts/find-unmentioned-classes.py`——**纯链接判定**（扫 `docs/zh/guide/*.md` 里所有指向 `../reference/*.md` 的链接，按页名反查，锚点/`./`/`../` 归一化），报「指南从没链到」的类页；正文写了类名但没挂链接**不算**命中，避免 `Helper`/`Base` 这类短名误判。首扫：**109 个类页 / 496 条指南→参考页链接 / 19 页从没被链到**（其中 17 页指南里连类名都没有）。补链 + 新增指南第 4-11 章后：**555 条链接、109/109 全被链到、孤儿 0**（`--all` 另列「只链 1 次」的 34 页，那是下一档的候选）。
        - 补链落点：`Core\ExitException`（exception.md：`use_exit_exception` 下 `SystemWrapper::exit()` 抛它、`ExceptionManager` 原样放行）、`Component\PagerInterface`（database.md 分页节）、`HttpServer\HttpServerInterface`（http-server.md「换实现」节）、`GlobalUser\User{LoginAction,LoginService}Interface`（user.md 选项表 + 参考手册行）、`GlobalAdmin\Admin{LoginAction,LoginService,Service}Interface`（admin.md 同构）、`Foundation\Business\Base`（layers.md 四层基类）、`Ext\RouteHookWebInstallerView`（installer.md §③）、`Ext\SqlDumperSupporterByPgsql`/`BySqlite`（database.md SQL 导出表——并**纠正**原先把三者并列的误导：默认映射只有 mysql 与 sqlite，pgsql 要自己配 `database_driver_SqlDumperSupporter_map`——**M14 已把 pgsql 补进默认映射，这条只作历史记录**）。
        - 7 个「指南从没写过的 Ext 类」另立一章：`guide/deprecated-exts.md`（4-11，122 行），判据是源码 `@todo deprecate`（`grep -rn` 命中 6 个类），`MiniRoute`/`Misc`/`ThrowOnTrait` 则如实标注「无废弃标记、但框架内部无使用点」。
    16. **全量覆盖测试 + `doced`→HEAD 参考手册同步（本轮；作者正在并行改同一工作区）**：
        - 测试：全量 `OK (96 tests, 823 assertions)`；`XDEBUG_MODE=coverage` + `tests/support.php` → `test_reports/index.html` **`Lines 4895/4895 (100.00%)`**（函数/方法、类/Trait 同为 100%；`src/` 109 个文件里 19 个是接口/空体类，本来就没有可执行行，见第 5 节）。**不要**用陈旧 dump 判断缺口：`test_coveragedumps/` 里会留着改名/移动前的旧类 dump（例如 `Component/RouteLister.php`），它们会把总数算歪——本轮先 `rm -rf test_coveragedumps` 再全量跑。聚合脚本见第 5 节新增的 `docs/scripts/covagg.php`（遍历全部 dump 并按源文件合并命中，数组型命中值要按 test 名取并集）。
        - 修 3 处源码：① `Admin::EVENT_ACTION_ADMIN_LOGED` → **`EVENT_ACTION_ADMIN_LOGINED`**（值本来就是 `'ACTION_ADMIN_LOGINED'`，而 `ControllerHelper` 引用的是 `Admin::EVENT_ACTION_ADMIN_LOGINED` ⇒ 常量名笔误；PHP 常量**懒求值**，类加载不报错、一被读到就 `Error: Undefined constant`；`GlobalAdmin::login()` 里的 `fire()` 同步改名）；② `GlobalAdmin/GlobalUser::mergeViewData()` 在 `$data['__logined_render_header_footer']` 为假时 `$header/$footer` **未定义**（会告警）→ 进 `if` 前先置 `null`；③ `GlobalUser::throwLoginOn()` 两个 `return` 补 `// @codeCoverageIgnore`（与 `GlobalAdmin` 镜像，`exit()` 之后那行本来不可达）。
        - 补覆盖的测试：`GlobalAdmin/GlobalUser` 的 `throwLoginOn()` 三条分支（自定义回调 / 非 Ajax 302 / Ajax JSON）、`DuckPhp::initComponentsOfRoot()` 的 redis 分支（配 `redis_list` 即触发，`RedisManager::init()` 只存配置不连接）、`ExtOptionsLoader::saveExtOptions()` 的抛异常分支（注意 `getRoot()` 返回的是**root 相位的 loader 组件**，读的是组件自己的 `data_file_enable`，不是 App 的）、`__logined_render_header_footer=false` 分支；`tests/Foundation/HelperTest.php` 的事件断言从「层 Helper 上的 `public static $EVENT_*` 属性」改成「层 Helper 上的**别名常量**」（`hasConstant` + 值等于 `User::`/`Admin::` 上的同名常量）。
        - `ZAllDemoTest` 的 `files` 期望长度 10567 → **10438**（`Admin`/`User` 落地为 root 组件 + `GlobalAdmin`/`GlobalUser` 瘦身后，选项表、容器类清单、包含文件表、调用栈行号都变）。**已 diff 两份 dump 确认差异只落在**：执行耗时、内存消耗、容器里的类清单（`GlobalAdmin`/`GlobalUser` → `Admin`/`User`）、调用栈行号、包含文件表——没有选项键层面的意外变化。
        - 参考手册：**新增 2 篇**（`GlobalAdmin-Admin`、`GlobalUser-User`）、**重写 2 篇**（`GlobalAdmin-GlobalAdmin`、`GlobalUser-GlobalUser`，旧页写的还是 `*_callback_for_*` / `go_url()` / `addExtViewData()` / `_Show()` 那套已删除的 API）、**更新 10 篇**（6 篇 GlobalAdmin/GlobalUser 接口页、`Core-App`、`Core-DuckPhpSystemException`、`Component-ExtOptionsLoader`、`DuckPhp`、`Ext-DuckPhpInstaller`、`Foundation-Business-BusinessHelper`、`Foundation-Controller-{AdminControllerBase,UserControllerBase,ControllerHelper}`）、**删除 2 篇**（`GlobalAdmin-AdminException`、`GlobalUser-UserException`——两个类已从源码删除，异常码/消息改由 `Admin::EXCEPTION_*` / `User::EXCEPTION_*` 常量承担）。
        - 生成页：`gen-options-docs.php` 的 `HIDDEN_DESC` 补两条（`url_admin_home`、`url_user_home`，否则 `--check` 报「隐藏选项缺少说明」），随后重生成 `index.md` / `options*.md`，`--check` → up to date。新页会被自动收进 `<!-- GEN:nav -->` / `<!-- GEN:az -->`，**不要手改** index.md。
        - **用户指南随后已补齐**（第 20 轮 / M11）：11 个 guide 文件先按「只记 TODO」处理（7 个 `//TODO` + 4 个零散旧名 + 6 处死链就地改成文字），紧接着的下一轮把它们全部改完（`user.md`/`admin.md` 重写、`events.md` 事件常量表重写等），`//TODO` 清零。清单与逐条原因见 `guide-maintenance-guide.md` §19（记录）与 §20（完成）。
    17. **M14 / 本轮：`SqlDumperSupporter` 默认映射漏了 pgsql（作者指出的真 bug）**：作者看到 `deprecated-exts.md` 里那句「`ByPgsql` 同样是方言实现，但默认映射里没有它：要自己加 `database_driver_SqlDumperSupporter_map`」，判定这**不该是文档要交代的事，而是源码的 bug**——框架自带 `Ext\SqlDumperSupporterByPgsql`，默认映射却只有 `mysql`/`sqlite`，于是 DSN 写 `pgsql:…` 时 `SqlDumper::exportScheme()` 之类会抛 `[pgsql]  No getSqlDumperSupporter`。
        - 修源码（1 行）：`src/Ext/SqlDumperSupporter.php` 的 `$options['database_driver_SqlDumperSupporter_map']` 补 `'pgsql' => SqlDumperSupporterByPgsql::class`（键就是 `DbManager::getDatabaseDriver()` 的返回值，来源是 DSN 里 `:` 前那段或 `database_driver` 选项）。
        - 补回归测试：`tests/Ext/SqlDumperSupporterTest.php` 原来只断言 mysql 能取到，现在把三个方言都断言一遍（`Current()` 返回 `ByMysql::_()` / `BySqlite::_()` / `ByPgsql` 实例）。**已按第 7 节那条规矩验证过「抓得住 bug」**：`git stash push -- src/Ext/SqlDumperSupporter.php` 把修复撤掉 → 测试红（`Exception: [pgsql]  No getSqlDumperSupporter`，指向 `SqlDumperSupporter.php:31`），`git stash pop` 还原 → 绿（4 断言，`SqlDumperSupporter` 8/8 行覆盖）。`ZAllDemoTest` 的 `files` 期望长度（10438）**没变**——demo 的选项 dump 里不含这个映射。
        - 文档同步：`guide/database.md`（SQL 导出表把三行并成一行：三个方言都在默认映射里，键就是 DSN 前缀）、`guide/deprecated-exts.md`（同一处，删掉「要自己加」的说法）、`reference/Ext-SqlDumperSupporter.md`（简介的「两个驱动」→三个、选项表默认值补 pgsql、注意事项里「例如 pgsql 需自己加入 map」改成「无匹配驱动抛 `[驱动名]  No getSqlDumperSupporter`」）、`reference/Ext-SqlDumperSupporterByPgsql.md`（「默认只有 mysql/sqlite」→「已在默认映射里，开箱即用」，示例改成覆盖映射换实现）。两份维护指南里那两条**历史记录**各加一句「M14 已补进默认映射，这条只作历史记录」，免得后来人被旧结论带偏。
        - 教训（值得记）：**「文档在教用户绕过源码缺陷」就是缺陷的信号**——凡是文档里出现「默认没有 X，要自己加/自己 hack」，先停下来问一句「这该不该是源码的事」。这次的修正成本是 src 一行 + 一条断言 + 4 处文档。
    18. **M16 / 本轮：清掉「`tests/data_for_tests/ZAllDemo`」这个从来不存在、也不会被任何检查抓出来的示例路径**（作者发现）：作者问「`tests/data_for_tests/ZAllDemo` 找不到，没在 git 里，需要重建？」——查证后**不需要重建，因为它从来就不存在**：`git log --all -- tests/data_for_tests/ZAllDemo*` 只有 `ZAllDemoTest.config.php` 与 `*.txt` 产物，磁盘上也没有（`tests/data_for_tests/` 下是 `Component/ Core/ Db/ DuckPhp/ Ext/ Foundation/ Helper/ HttpServer/ ZThirdDemo/`），不被 `.gitignore` 忽略——是写章节时按测试名 `ZAllDemoTest` 拼出来的假路径。当年有一轮已经发现过其中一处是假的（`ZAllDemo/src/Controller/Helper.php`），只改了一处，其余二十来处留到本轮。
        - 真实资产：五层骨架片段来自 **`skeleton/`**（`YourProjectName` 命名空间与 `DemoBusiness::_()->foo()` 等逐字对得上）；「第二卷示例应用」是 **`demo/`**（`tests/ZAllDemoTest.php` 起内置服务器 + curl 各路由比长度，宿主一直是 `demo/`）；「示例首页 dump options/单例」是 **`demo/view/files.php`**（`/files` 路由）。
        - 改动 25 处、只动 `docs/`：指南 11 个文件 + 账本 4 个文件（`guide-maintenance-guide.md` 硬约束 2/资产表/模板/§6/§10、`guide-rewrite-checklist.md`、本文件 §7 陷阱表与历史记录、`helper-merge-checklist.md`）。**`ZAllDemoTest*` 这些真文件一律没动**。逐条清单见 `guide-maintenance-guide.md` §24。
        - **教训（已写进第 7 节陷阱表）**：引用路径前先 `Test-Path` / `git ls-files` 验证存在——**死链检查只查 `.md` 之间的链接，查不出「正文里写的仓库路径是假的」**，这种错误不报错、只骗读者。
- **待办（本工作范围外）**：
  - `Ext/PermissionMenu` 的进一步调整（作者说自己稍后再看）；
  - ~~`docs/zh/guide/external-auth.md` 里还有一批旧键名未校~~ **已处理**：该章已拆成 `session.md`(2-9)/`user.md`(2-18)/`admin.md`(2-19)，键名与选项按当前源码逐条核对（旧键 `user_callback_get_*`、已废选项 `user_provider`/`admin_provider`/`*_default_exception_class` 都已在正文标注失效）；
  - `docs/zh/reference/index.md` 目录页：新增文档已全部登记（含本轮 2 篇新页），剩下的是**逐条核对说明文字**是否仍准确；
  - `options.md` / `options-by-class.md` / `options-index.md` 三个汇总页已改由 `gen-options-docs.php` 生成，`--check` 为 up to date；
  - **`docs/zh/guide/` 的指南补齐**：第 16 轮留下的 7 个 `//TODO` + 4 个零散旧名**已全部处理完**（第 20 轮 / M11，见 `guide-maintenance-guide.md` §20：2-18/2-19 两章重写、2-12 事件常量表重写、3-5 视图级开关、附录 B 登录片段、2-11 登录/权限表述）。参考页与指南现在同源一致，`grep -rn '//TODO（参考手册同步轮' docs/zh/guide` = 0。
  - **指南侧的「用/实现」分章**（第 21 轮 / M12，作者裁定）：2-18/2-19 只讲**怎么用**（`Helper::User*` / `Helper::Admin*` 入口、未登录表现、视图开关），**新写 4-12 `impl-user.md` / 4-13 `impl-admin.md`** 承接接入实现（三件实现 + 选项表 + `ext` 挂载）；写这两个体系的参考页时，示例应按「用侧链 2-18/2-19、实现侧链 4-12/4-13」交叉引用。
  - **卷二章号在 M13 又重排过一次**（第 22 轮，作者裁定）：原 2-10「请求生命周期与钩子点」拆成 **2-2 请求生命周期**（`lifecycle.md`，含内置组件清单）+ **2-4 路由钩子**（`route-hooks.md`，排在 2-3 路由进阶之后），其后各章顺次后移 ⇒ 现在是 `2-1`–`2-20`、全书 **47 章**；用侧两章随之变成 **2-19/2-20**。参考页里若要写「见指南第 N 章」，按新号写（老记录里的 2-1x 是当时的编号）。
  - **M14 修了 `SqlDumperSupporter` 漏配 pgsql 的源码 bug**（第 23 轮，作者裁定）：默认映射现在含 `mysql`/`sqlite`/`pgsql` 三个方言，`tests/Ext/SqlDumperSupporterTest.php` 三个都断言；再看到「某内置实现要自己注册才生效」的说法，先当成源码 bug 查（见第 17 条）。
  - **M15 把 1-3 与 2-1 合并**（同一轮，作者裁定）：`project-structure.md`（1-3）改成「目录结构与四层架构」（330 行，四层规范整章并入）；`layers.md`（2-1）只剩 38 行指路页 + 五层速查表 + 本卷地图，**目的是保住 2-1 这个号**（33 处「第 2-1 章」引用与全书 47 章都不必动）。参考页里若要指「四层规范」，链接文字写「第 1-3 章 目录结构与四层架构」。
- **生成器已知缺陷（如需修复）**：`docs/scripts/gen-reference.php verify` 对含 trait 别名 override 的大文件（`Core/App.php`）会漏列方法；修好前请以 `drift.py` 为准。

## 9. 快速自检（冒烟）

```powershell
$env:WSL_UTF8=1   # 一次即可，避免 wsl 输出乱码

# 1) 全量漂移：改完文档后应无 missing-*（extra 只会是示例方法）
python %TEMP%\drift.py --all

# 2) 编码抽查：把下面脚本存成 %TEMP%\enc.py 后运行
python %TEMP%\enc.py

# 2b) 孤儿页反查（指南有没有链到每个参考页；期望「从没被链到: 0」）
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/find-unmentioned-classes.py"

# 2c) 提交前：这次改动里有没有「只有排版」的文件（编辑器/并行会话改的噪声，直接 git checkout 丢掉）
python3 docs/scripts/check-md-layout.py

# 3) 本次改了 src/ 或 tests/ 时（一律走 WSL，按单个测试文件跑，见第 5 节末）：
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Component/CommandTest.php"
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && bash docs/scripts/check-non-ascii.sh"   # 期望 Total non-ASCII lines: 0
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

## 11. 第 15 轮：Helper 合并 / 改名后的参考手册同步（阶段四 + 第 4 轮需求）

**背景**：`src/Helper/*HelperTrait` 并进 Foundation 之后，作者又改了两轮命名——层 Helper 类改成 `Foundation\<层>\<层>Helper`，并集改由 `Foundation\Helper` / `DuckPhpAllInOne` 的 `__callStatic` 承接；`ModelHelperTrait` 落回 `DuckPhp\Foundation\Model\ModelHelperTrait`。

**做法（可复用）**

1. 层页与 trait 页**用 `git mv -f` 互换**：trait 页带着方法表直接变成新类名页，旧类页删掉——方法表不用手搬。
2. 并集两页（`Foundation-Helper.md`、`DuckPhpAllInOne.md`）手写重写：写清派发顺序、12 个重名方法的胜出方、`@method` 注释的定位（IDE/静态分析可见，反射与 `method_exists()` 不可见）。
3. 全库替换用**一次成对替换**（`DuckPhp\Helper\*` → 新类名、页文件名 → 新页名），53 个 md 命中；**账本类文档先排除**（本文件、`guide-maintenance-guide.md`、两份 checklist），它们的旧名是沿革记录，单独手改。
4. 同一轮清掉两类「别人的漂移」：master 的类移动（`ModelTrait`→`Model\ModelTrait`、`SessionTrait`/`ExceptionReporterTrait`→`Controller\*`、`Core\ThrowOnTrait`→`Ext\ThrowOnTrait`、`Foundation\ExceptionTrait` 删除）与**对话外代码改动**（`IsRealDebug`→`IsHiddenDebug`、`Component\RouteLister`→`Ext\RouteLister`、`use_user_view`/`use_admin_view`/`use_*_view_header_footer` 四个选项全失效）。
5. 索引：先在 `gen-options-docs.php` 里删掉失效选项的硬编码行、补 `not_empty` 的说明，再重生成 `index.md`/`options.md`/`options-by-class.md`；`--check` 必须 up to date。

**验收**：`check-doc-links.py docs/zh` → 1972 条链接 0 死链；漂移扫描只剩 3 条已判读的假报（函数文件/多声明文件的标题核对、`DuckPhpAllInOne.md` 的 embedMe 键表与示例方法）；参考页 **118 → 114**（删 3 个旧类页 + `Foundation-ExceptionTrait.md`，另 8 处改名）。