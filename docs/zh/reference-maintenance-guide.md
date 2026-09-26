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
   - **类改名 / 换目录 / 拆并类** → 页文件用 `git mv -f` 搬过去（**方法表不用手搬**），再改 H1、交叉引用与 `index.md` 的 `<!-- GEN:nav -->`/`<!-- GEN:az -->` 块（后者由 `gen-options-docs.php` 重生成，别手改）；旧页确实作废才 `git rm`。两个类合并成一页、或一页拆两页时，不要留「本页已合并到 X」的空壳页——直接删，让链到它的地方改指新页。
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
| 把 `@method` 当成真方法写 | `Foundation\Helper` / `DuckPhpAllInOne` 的并集是 **`__callStatic` 魔术派发**：反射、`method_exists()`、`is_callable()` **都看不到**那些名字，只有 96 条 `@method` 注释给 IDE/静态分析看。写这两页时要说清派发顺序、重名方法谁胜出，并明说「`@method` 不是真方法」。 |
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
| **写死的「类名单 / 文件清单」会随改名过期** | 实测：`tests/Ext/DuckPhpInstallerTest.php` 写死一张 15 个类的名单来断言「生成的骨架类都能加载」，作者把 `skeleton` 的 `ExceptionReporter` 改名并挪到 `Controller/ExceptionAction` 之后，名字在生成工程里不存在 ⇒ 该断言一直红，直到下一次全量跑才暴露（单文件跑测试不会碰它）。改法：**从生成目录 `glob('src/*/*.php')` 推类名**（文件 basename 即类名、子目录即子命名空间），以后改名自动跟着走。凡文档/测试里写死的清单都应这样对待——**跨文件的漂移只有全量跑才看得见**。 |
| 指南里的示例没实跑过 | 本仓约定：指南与参考页的示例必须能跑。第三卷（3-1–3-7 章）全部挂在 `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php`（36 断言）上，改示例就重跑它；第一/二卷的兜底是 `demo/`（`tests/ZAllDemoTest.php` 起内置服务器跑它）与 `skeleton/`（脚手架骨架）。**别引用 `tests/data_for_tests/ZAllDemo`——那个目录从未存在。** |
| **正文里写的仓库路径是假的** | 死链检查只查 `.md` 之间的链接，**查不出「正文里引用的源码/测试路径不存在」**——它不报错，只骗读者。实测：`tests/data_for_tests/ZAllDemo` 被指南引用了 25 处（五层骨架、示例应用、dump 页……），而**这个目录从未进过 git、磁盘上也没有**（是写章节时按测试名 `ZAllDemoTest` 拼出来的）；同类还有 `layers.md` 的 `ZAllDemo/src/Controller/Helper.php`。**规矩：引用路径前先 `Test-Path`（或 `git ls-files`）验证一次**；批量核查看正文里的反引号路径（`grep -o '`[a-z][^`]*\.php`'`）。 |
| 多应用相关的斜杠坑（写示例时必踩） | ① `RouteHookRewrite::assignRewrite()` 的**键必须带前导 `/`**；② `controller_resource_prefix`：根应用写 `'/res/'`、子应用写 `'res/'`（前缀按 `'/'.controller_url_prefix.controller_resource_prefix` 拼，子应用的挂载前缀已带尾斜杠，再带前导斜杠就变成 `//`）；③ 相位名不是类名（子应用是 `:<name>`，不是 `\X\System\App`）。 |
| **选项写成裸类名 / 报告器类少个 `_()`——启动都不报错，异常真抛出来才炸** | 实测过的两个真 bug，都属于「只看形状的检查骗人」：① `exception_reporter` 写 `ExceptionAction::class`（裸类名）时 `is_callable()` = **false** ⇒ 启动即抛 `'exception_reporter' config error!`，必须写 `[ExceptionAction::class, 'OnException']`（类名里有没有静态 `OnException()` 不影响这个判断）；② `ExceptionReporterTrait::OnException()` 内部是 `static::_()->_OnException($ex)`，而该 Trait **不 use 任何单例 Trait** ⇒ 组合方（`skeleton`/`demo` 的报告器、你照抄写的类）必须自己 `use DuckPhp\Foundation\SingletonTrait;`（或继承 `Controller\Base`），否则异常抛出的那一刻报 `Error: Call to undefined method …::_()`。**规矩：文档里写「可调用选项 / 骨架类」时，先在 `php -r` 里真调一次**（`is_callable()` / `class_exists()` 不算验证）。 |

## 8. 当前状态与待办

> 每轮只更新这一段，**别堆流水账**；过程与逐文件清单属于 commit message 与对应文档，历史看 `git log`。

- **现状**
  - `docs/zh/reference/` 共 **114 篇**：110 篇逐类文档 + 4 个汇总页（`index.md`、`options.md`、`options-by-class.md`、`options-index.md`）。逐类文档全部按第 3 节模板。
  - 漂移扫描 `drift.py --all` **只剩 3 条已判读的假报**：`Core/Functions.php` 与 `Ext/RouteHookWebInstallerView.php` 的 `HEAD-MISMATCH`（函数文件/无类声明的文件，脚本局限），`DuckPhpAllInOne.php` 的 `extra-option` + `extra-method`（embedMe 键表与「使用方式」示例里的自定义方法）。
  - 站内链接 0 死链；114 篇全 UTF-8；`src/` 非 ASCII 0 行；`gen-options-docs.php --check` up to date。
  - 测试基线（WSL，2026-09-26 全量实测）：`php vendor/bin/phpunit --no-coverage` → **`OK (97 tests, 829 assertions)`**；覆盖率 **`4895/4895 (100.00%)`**（`XDEBUG_MODE=coverage` 跑完全量后，再跑 `tests/support.php` 生成 `test_reports/index.html`；聚合判定用 `docs/scripts/covagg.php`）。⚠️ 中途 Fatal 的测试不写自己的 dump ⇒ 覆盖率会假降（实测见过 `97.47%`），**先确认全量没有红**。`tests/data_for_tests/ZAllDemoTest.config.php` 里 `files` 的期望长度是 **10432**（跟当前工作区的选项表绑定，见第 7 节那一行）。
  - 同步基线：分支 `doced`（= `3ece976b`）。下次同步从它之后算起（见第 9 节末的提示）。
  - **默认不动**：`docs/en/`（陈旧英文副本）、`docs/old/`、`docs/duckphp.gv`（陈旧生成物）、`README*.md`——它们不随中文文档同步。
- **待办**
  - `Ext/PermissionMenu` 的进一步调整（作者说自己稍后再看）；
  - `docs/zh/reference/index.md` 目录页的说明文字逐条核对（新页都已自动登记进 `<!-- GEN:nav -->`/`<!-- GEN:az -->`，剩下的是「一句话说明是否仍准确」）。
- **生成器已知缺陷**：`docs/scripts/gen-reference.php verify` 对含 trait 别名 override 的大文件（`Core/App.php`）会漏列方法；而且它只认**反引号开头**的方法条目（本仓按第 3 节用 4 空格缩进 → 它会把几乎每篇都报成 `method missing in md`，属假报，实测全量 1022 条）。判一致性以 `drift.py` 为准（见第 10 节）。

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

# 2d) 改了 demo/ 或 skeleton/ 时（含报告器、选项示例）：这两个是现成的端到端冒烟
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZAllDemoTest.php"
wsl bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Foundation/Controller/ExceptionReporterTraitTest.php"
# 报告器这种「配置对了、调用时才炸」的东西（第 7 节新增的那行陷阱），另在 php -r 里真调一次；
# 内联 php -r 的引号在 PowerShell→wsl 两层里很容易打架，存成临时 .php 再跑更稳。

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

**另一类假报来自 `gen-reference.php verify` 本身**：它读 md 里的方法条目时要求**行首是反引号**，而本仓按第 3 节统一用 4 空格缩进 ⇒ `verify --all` 会把几乎每篇都报成 `method missing in md`（实测 1022 条），**别照着它删改**。它反过来还会**漏掉真过期条目**（文档里写着、源码已删的方法）：`Foundation-Controller-ExceptionReporterTrait.md` 的 `defaultException()` / `defaultSystemException()` 就是这样存活了很久（该页方法列表同样是 4 空格缩进，扫描器压根没读到），最后靠人对着源码重写才发现。**判一致性只认 `drift.py`**，`verify` 只在修某个具体页时当参考。
