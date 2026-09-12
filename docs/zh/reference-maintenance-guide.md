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
  - 检查命令：`bash scripts/check-non-ascii.sh`（等价于 `grep -rnP '[^\x00-\x7F]' src/ --include='*.php'`）。
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
    tagline = 'UPDATE' if (miss or omiss) else 'ok'
    print('%-6s %-50s missing-method=%s missing-option=%s' % (tagline, f, miss, omiss))
    # extra 多为「使用方式」示例里自定义的方法，通常无需处理
```

**判读**：`missing-method` / `missing-option` 非空 = 文档确实缺内容，必须补；`extra`（脚本不打印，可自行加）通常是示例代码里的自定义方法，忽略。同一键合并成一行（如 `| \`a\` / \`b\` |`）会被 doc_opts 漏读 → 假报 `missing-option`，拆成单键行即可。

## 6. 标准工作流

1. **找漂移**
   ```powershell
   git diff --name-only <上次基线tag> HEAD -- src      # 或直接 python %TEMP%\drift.py --all 看全量
   python %TEMP%\drift.py <上次基线tag>
   ```
2. **逐个改动文件更新文档**（按第 3 节模板）：
   - 新增方法 → 在方法列表按**可见性分组**补条目（签名 + 一句说明）；新增选项 → 选项表与「全部选项」块都补；类声明变化（`implements`/`extends`/`use`）→ 改「类信息」；行为变化 → 改对应方法说明与「注意事项」。
   - 删/改名 → 同步删除或改名，并在说明里注明（如 `urlForRegist` → `urlForRegister`、`user_url_regist` → `user_url_register`）。
3. **校验**：重跑 `drift.py` 确认 `missing-*` 为空；再抽查新段落。若本次也改了 `src/` 代码，**必须再跑** `bash scripts/check-non-ascii.sh` 并确认输出 `Total non-ASCII lines: 0`（见第 4 节的硬性规则）。
4. **登记**：勾掉/更新本指南第 8 节的“状态与待办”（若有新增文档，也在第 8 节记一句改动清单即可）。
5. **提交**：只 add 相关路径
   ```powershell
   git add docs/zh/reference
   git commit -m "docs: sync reference with src (<tag>..HEAD)"
   ```
   ⚠️ 仓库根有一批与本工作无关的未跟踪目录（`.obsidian/`、`vendor-bak/`、`bin/*.php`、`reasonix.toml` 等），**不要 `git add .`**。

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

## 8. 当前状态与待办

- **已完成**
  - 110 篇逐类文档全部按本指南规范重写；`drift` 全量扫描 **0 不一致**。
  - 一次“`ref-lastdoc` → HEAD”的增量同步：
    - **新增 8 篇**：`GlobalAdmin-AdminLoginActionInterface.md`、`GlobalAdmin-AdminLoginServiceInterface.md`、`GlobalAdmin-AdminSessionInterface.md`、`GlobalAdmin-AdminSessionTrait.md`、`GlobalUser-UserLoginActionInterface.md`、`GlobalUser-UserLoginServiceInterface.md`、`GlobalUser-UserSessionInterface.md`、`GlobalUser-UserSessionTrait.md`；
    - **更新 20 篇**：GlobalAdmin/GlobalUser 主文档（接口、事件常量、新选项、`login/logout/register`、会话优先、`_Show` 细节），`UserActionInterface`（`urlForRegist`→`urlForRegister`），`UserException`（改继承 `\Exception`），`Core-CoreHelper`（`ChildCall/ProjectThrowOn`、`exception_map`），`Core-Route`（`runFinallyHooks`→`clear`），`Core-App`（`setting`/`exception_map` 选项），`Core-KernelTrait`（finally 改调 `Route::clear()`），`DuckPhp`（`use_user_view/use_admin_view` 改为手动开启），`DuckPhpAllInOne` 与 `Foundation-Helper`（insteadof 清单），`Component-RouteLister`、`Component-RouteHookResource`、`Core-View`、`Helper-App/Business/ControllerHelperTrait`。
  - 后续把 7 篇旧式排版（`Component-DbManager`/`Pager`/`RouteHookPathInfoCompat`/`RouteHookRewrite`/`RouteHookRouteMap`、`Core-SuperGlobal`/`SystemWrapper`）与 4 篇合并表格行（`Ext-CallableView`、`Ext-RouteHookWebInstaller`、`GlobalAdmin`、`GlobalUser`）统一为规范格式——`drift` 因此从 11 处假报收敛到 0。
- **待办（本工作范围外）**：
  - `docs/zh/reference/index.md` 目录页与各篇文件名/说明的核对（含上面 8 篇新文档尚未登记进目录页）；
  - `options.md` / `options-by-class.md` / `options-index.md` 三个汇总页（内容过时且行文损坏，建议改为由脚本生成）；
  - `docs/zh/guide/` 教程与 reference 的交叉引用校对。
- **生成器已知缺陷（如需修复）**：`scripts/gen-reference.php verify` 对含 trait 别名 override 的大文件（`Core/App.php`）会漏列方法；修好前请以 `drift.py` 为准。

## 9. 快速自检（冒烟）

```powershell
# 1) 全量漂移：改完文档后应无 missing-*（extra 只会是示例方法）
python %TEMP%\drift.py --all

# 2) 编码抽查：把下面脚本存成 %TEMP%\enc.py 后运行
python %TEMP%\enc.py
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

> 提示：本仓库的历史 tag `ref-lastdoc` 是上一轮文档同步的基线；做下一次同步时，把上面命令中的 `<tag>` 换成“上次同步时的 commit/tag”即可。

## 10. 扫描器报 missing 时怎么判读（判假报 vs 真缺）

`drift.py` 报 `missing-*` 时，先分清是**假报**还是**真缺**：

**常见假报（文档其实有，只是形式让脚本读不到）**

- 选项表把多个键写在**同一行**（`` | `a` / `b` | ``）：脚本只按“一行一键”匹配 → 拆成一行一键即可。
- 方法条目**缺 `function` 或可见性词**（如 `    static PageNo(...)`），或把静态壳与实例实现**合并成一行**（如 `DbForWrite() / _DbForWrite()`）：脚本按「可见性 + `function`」匹配 → 按第 3 节拆成规范条目。
- 键名含连字符（如 `psr-4`）：早期脚本会误判，**当前脚本已支持**。

**判定为真缺（必须补）**：以上形式都正常、且该名字在源码里确实存在时，就是文档少写了条目/选项。

**历史记录（此前的假报已全部清零）**：脚本首次全量扫描报出 15 处，其中 2 处是真缺（`Component-Configer.md` 缺 `path` 选项 + 整个「全部选项」节、`Core-Logger.md` 缺 `path` 表格行），另外 13 处即上述两类形式假报。这些**现已全部修复**（统一格式 + 补齐真缺），`drift.py --all` 现输出 **0 假报**。
