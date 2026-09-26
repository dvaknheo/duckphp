# 用户指南维护指南

> 目标读者：维护 `docs/zh/guide/`（用户指南）的 AI / 工程师。
> 前提：**指南已交付**——一页总目录 + 四卷 47 章 + 4 附录，站内 0 死链、章号一致、示例全部指向现成资产。本文件讲**以后怎么改**，不重复交付过程。
> 姊妹文件：[参考手册维护指南](reference-maintenance-guide.md)（`docs/zh/reference/` 侧）；两份共用一套校验命令与坑表。
> **章号 → 文件、每章一句话的唯一事实来源是总目录** [`guide/index.md`](guide/index.md)；本文件只在第 7 节记号形式与改号姿势。

---

## 1. 全书面貌（别改坏的结构）

**梯度**：入门 → 单一应用 → 使用第三方应用 → 高级话题。

| 卷 | 读者状态 | 读完能做什么 | 章数 |
|---|---|---|---|
| 一 入门 | 没装过 | 跑起一个页面并上线 | 7 |
| 二 单一应用 | 会跑 | 独立写完一个业务应用（覆盖主流框架的全部常见主题） | 20（含 2-2 请求生命周期、2-3/2-4 路由进阶与钩子、2-19/2-20「使用用户/管理员系统」） |
| 三 使用第三方应用 | 会写单应用 | 把外部应用挂进来、共享组件、覆盖它的内容 | 7 |
| 四 高级话题 | 会集成 | 改框架行为、调优、排错、维护文档、自己实现用户/管理员体系、看清 `Ext\*` 那一族 | 13（含 4-11/4-12「实现用户/管理员系统」与 [4-13「`Ext\*` 扩展类」](guide/ext-classes.md)） |

**硬约束**（作者已裁定，别自行放宽）：

1. **一页总目录** = `docs/zh/guide/index.md`；`docs/zh/index.md` 只做指路，不复制章表。
2. **示例只用现有代码**：`demo/`（多入口示例应用）、`skeleton/`（脚手架骨架）、`tests/data_for_tests/ZThirdDemo`（第三卷示例工程），以及 `tests/data_for_tests/` 下的测试夹具；**不新建示例工程**（`ZThirdDemo` 是唯一获准新增的）。⚠️ 老文档里写的 `tests/data_for_tests/ZAllDemo` **从未存在过**（是写章节时按测试名 `ZAllDemoTest` 拼出来的假路径）——**引用任何路径前先验证它存在**（`git ls-files`/`Test-Path`），死链检查只看 `.md` 之间的链接，查不出正文里的假路径。另外：**文件级目录树与工程约定只写在 `skeleton/AGENTS.md` 一份**（改名/加文件都会牵动它，改完跑 `docs/scripts/check-skeleton-tree.py`）。
3. **章 ≤400 行**；明显超长的要拆。拆/并章要连带改总目录、全库章号引用、参考页里的「见第 N 章」。
4. **附录**：指南侧不再有 `appendix-global-functions.md` / `appendix-options.md`——全局函数参考在 `reference/Core-Functions.md`，应用选项参考在 `reference/options.md` / `options-by-class.md` / `options-index.md`；指南里只留指向参考手册的链接。
5. **不写**「升级与破坏性变更」章/附录。
6. **章号一律用「卷-章」号**：`1-1`–`1-7`、`2-1`–`2-20`、`3-1`–`3-7`、`4-1`–`4-13`（附录仍是 A/B/C/D）。**不要再出现单数字章号**。
7. **类名第一次出现时，链接到参考手册对应页**（如首次写 `Route::_()` 处链成 `[Route](reference/Core-Route.md)`；`Base`/`Helper` 有多个同名页，必须用全限定名形式）。
8. **不写旧文档的过时内容**：`architecture.md`/`components.md`/两篇旧附录都已删除，指南里不要再出现「旧文档里是这么写的」「已废弃的 X」之类表述——只写当前事实，必要时直接给正确写法。
9. `skeleton/` 与 `demo/` 是**活着的模板工程**：改它们同样要跑测试（`tests/ZAllDemoTest.php` 覆盖 `demo/`；`tests/Foundation/ExceptionTraitTest.php` 对 `skeleton/` 的 `ProjectException` 有防回归断言），骨架里的失真内容（旧选项名、方法前缀默认值等）要顺手修。
10. **「怎么用」与「怎么实现」分章**（作者裁定）：2-19/2-20 只讲**调用方怎么用**（Helper 入口、未登录表现、视图开关）；「接入实现」（三个实现 + 选项 + `ext` 挂载）在 4-11/4-12。别把选项表塞回 2-19/2-20。
11. **照抄 `skeleton/`、`demo/` 的代码前先真的跑一次**：把骨架片段写进指南时，先在 `php -r` 里把它调通。`is_callable()`、`class_exists()` 这类只校验「形状」的检查会骗人——选项写成裸类名时启动不报错、报告器类少个 `_()` 时启动也不报错，**都要等异常真抛出来才炸**。示例里的选项值一律写 `[类::class, '方法']`（或闭包），别写裸类名。

## 2. 每章模板（全卷统一，照抄结构）

```
# <卷-章号> <标题>

> 解决什么问题 · 前置章节 · 预计阅读时间
> （若本章有示例工程）一句话说明示例在哪、怎么跑

## 最小示例          ← 能跑；标明来源（`demo/` 或 `skeleton/` 或 `ZThirdDemo`）
## 机制说明          ← 讲为什么；链到 reference 对应篇
## 常见写法          ← 3~5 个片段
## 常见错误          ← 表格：现象 / 原因 / 改法
## 下一步            ← 前后章 + 相关参考
```

- 术语只用 [附录 A 术语表](guide/appendix-glossary.md) 定下的叫法（应用 / 子应用 / 相位 / 挂载前缀 / 共享容器 / 覆盖 / Helper / 扩展）。
- 示例里统一用 `::_()`、`Helper::`、`__h()`；配置示例统一用 `MyProj` 作命名空间。
- 章内引用 reference 的链接必须**指向真实存在的文件**（用 §4 的脚本查）。
- **代码块里不要插链接**（改号/加链脚本要跳过围栏代码块，否则示例没法直接复制）；类名链接放在正文或行内代码里。
- 章号引用写法：`第 2-4 章`、范围写 `第 2-1–2-20 章`；链接文字带章号时目标必须与章号一致（§4 有反查办法）。

参考样板：[第 3-1 章](guide/advanced-phase.md)（机制讲清 + 坑位标注）与 [第 3-5 章](guide/overriding.md)（含「谁赢」的排查思路）。

## 3. 示例与验证资产

| 资产 | 服务哪几卷 | 怎么跑 |
|---|---|---|
| `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php` | 第三卷 3-1–3-7 | `wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` |
| `demo/` + `tests/ZAllDemoTest.php` | 第二卷（四层/视图/路由/多入口） | `wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/ZAllDemoTest.php"`；它「起内置服务器 + curl 各路由比字节长度」，改 `src/` 或 `demo/` 后长度会变（见 §5）。被测宿主一直是 `demo/` |
| `demo/`（`public/` 多入口 + `src/System/AppWithAllOptions.php`） | 第一卷、第二卷 | `php -S 127.0.0.1:8080 -t demo/public` 后访问各入口 |
| `skeleton/` | 第一卷（1-3 目录结构与四层架构）、新工程起步 | 读代码；改动后跑 `tests/Foundation/ExceptionTraitTest.php`、`tests/ZAllDemoTest.php`、`python3 docs/scripts/check-skeleton-tree.py` |
| `docs/scripts/`（文档工具：链接检查、排版检查、非 ASCII、孤儿页反查、骨架目录树、选项/参考页生成） | 全卷校验 | 见 §4；**脚本随文档一起提交**，都从仓库根目录跑 |

**分工（别写串）**：框架怎么用 → `docs/zh/guide/`；某方法签名/选项默认值 → `docs/zh/reference/`；**「这个工程里有哪些文件、该建什么、不许做什么」→ 只写在 `skeleton/AGENTS.md` 一份**（它随包发给用户，生成工程时就在用户项目根目录）。1-3 章讲目录与四层的**机制**，文件级清单别再抄一份；README 只画顶层目录。

**约定：章里每一段示例代码，都要能在上述资产里指出出处，或已实跑过。** 第三卷的 7 章就是这么做的——每条章内结论都对应 `ZThirdDemoTest` 里的一条断言。

## 4. 校验命令（每改完一章就跑）

```powershell
$env:WSL_UTF8=1      # 一次即可，避免 wsl 输出乱码

# ① 全仓 md 相对链接是否都存在（含新章、TOC、迁入页）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/check-doc-links.py docs"

# ② 新文件编码（GBK 会被报出来）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 - <<'EOF'
import glob,io
bad=[]
for p in glob.glob('docs/zh/guide/*.md')+glob.glob('docs/zh/reference/*.md'):
    try: io.open(p,encoding='utf-8').read()
    except UnicodeDecodeError: bad.append(p)
print('non-utf8:', bad or 'none')
EOF"

# ③ 章 ≤400 行
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && wc -l docs/zh/guide/*.md | sort -n | tail -20"

# ③b 提交前：这次改动里有没有「只有排版」的文件（别人/别的编辑器改的噪声）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/check-md-layout.py"

# ③c 章号一致性：扫 `docs/zh/guide/*.md` 里「链接文字带章号」的引用，按「文件名 → 章号」表反查，期望 0 处不一致
#     （没有现成脚本，让 AI 现写一个十几行的即可；判据见 §7）

# ③d 改过 skeleton/ 就跑：随工程的 AGENTS.md 里那张目录树必须与实际文件一致（双向比对，可 --fix）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/check-skeleton-tree.py"

# ④ 改过 src/ 或 tests/ 时：跑相关单测；改过示例就重跑示例测试
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"

# ⑤ 收尾（全量，约 5.5 分钟，建议后台跑）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage"
```

**当前基线**（2026-09-26 全量实测）：

- 全量 `php vendor/bin/phpunit --no-coverage` → **`OK (95 tests, 875 assertions)`**（约 6 分钟）；
- 覆盖率 **`4735/4735 (100.00%)`**：`XDEBUG_MODE=coverage php vendor/bin/phpunit`（跑全量、顺便写 `test_coveragedumps/`）→ 再 `XDEBUG_MODE=coverage php vendor/bin/phpunit tests/support.php` 生成 `test_reports/index.html`；判「有没有漏测」用 `php docs/scripts/covagg.php`（按源文件合并全部 dump，输出 `TOTAL … lines x/y` 与有缺口的文件）。⚠️ **中途 Fatal 的测试不会写自己的 dump**，覆盖率会假降（实测见过 `4774/4898 (97.47%)`）——先确认全量没有红，再信覆盖率；⚠️ 删过类/改过测试文件后，`test_coveragedumps/` 里的旧 dump 会让 covagg 的总数虚高，**先 `rm -rf test_coveragedumps` 再跑全量**；
- `docs/zh` 相对链接 **0 坏链**、`docs/` 下只有归档目录 `docs/old/` 与陈旧副本 `docs/en/` 还有历史坏链（不属本工作范围）；[`find-unmentioned-classes.py`](../scripts/find-unmentioned-classes.py) → 107 个类页全部被指南链到、孤儿 0。

`docs/scripts/check-doc-links.py`（扫 `docs/**/*.md` 的相对链接，忽略外链与锚点，恒退出 0——读打印的 `broken: N`）：

```python
import glob, io, os, re, sys

roots = sys.argv[1:] or ['docs']
bad, total = [], 0
for root in roots:
    for p in sorted(glob.glob(root + '/**/*.md', recursive=True)):
        d = os.path.dirname(p)
        for target in re.findall(r'\]\(([^)\s]+)\)', io.open(p, encoding='utf-8').read()):
            if target.startswith(('http://', 'https://', 'mailto:', '#')):
                continue
            target = target.split('#')[0]
            if not target or not target.endswith('.md'):
                continue
            total += 1
            if not os.path.exists(os.path.normpath(os.path.join(d, target))):
                bad.append((p, target))
print('checked %d relative md links, broken: %d' % (total, len(bad)))
for p, t in bad:
    print('  %s -> %s' % (p, t))
```

## 5. 已知坑（都已踩过）

| 坑 | 说明 |
|---|---|
| **未写的章不要挂链接** | 还没落稿的章在总目录里用**纯文本 + `⏳`**、不挂链接（写完再换成链接），否则链接校验一直红。当前 47 章 + 4 附录已全部落稿，所以 `⏳` 只会出现在**新增**章节还没写完的时候。 |
| 旧章里的 API 可能早就不存在 | 实测：`advanced-phase.md` 有 3 处 `App::Root()->getOverridingClass()`（源码里没有）；`helper.md` 的 `assignRewrite('article/123', …)` 少了前导 `/` 因而永不命中；`Configer` 读 `config/<名>.php` 而不是 `<名>.config.php`。**改之前先核对源码。** |
| **正文里写的路径/类名可能是假的** | 死链检查查不出这一类。实测 `tests/data_for_tests/ZAllDemo` 被引用了 25 处而它**从未存在**；同类还有 `layers.md` 里假想出来的 `ZAllDemo/src/Controller/Helper.php`。**引用前先 `git ls-files` / `Test-Path`；示例里的类名先 `class_exists()`。** |
| **示例「能跑」的标准是真调用一次** | 只做静态检查（类存在、`is_callable()` 为真）会放过两种必炸写法：选项写裸类名（`ExceptionAction::class` 不是 callable，启动即抛 `config error`）、报告器类少 `_()`（`ExceptionReporterTrait::OnException()` 内部是 `static::_()->…`，异常真抛出时 `Call to undefined method …::_()`）。见硬约束 11。 |
| **批量替换 / 改章号** | 必须**单遍替换 + 回调映射**（`re.subn`）：顺序 `sed` 会链式误改（`14→8` 之后 `8→17` 又把它改走）。**多键对调**（如 2-3 ↔ 2-4）要各用**独立占位符**，或按链接目标反推——共用一个占位符会把刚插入的文字再匹配一次，30 处引用全被改回去。替换范围也要收窄：「表格行首列是数字」的规则全库套用会误伤 `reference/setting.md`、`i18n.md` 里「次序 1/2/3」的普通编号表（**表格行只对总目录生效、H1 只对 `docs/zh/guide/` 生效**）；加链接的脚本必须跳过围栏代码块（第一版把 76 处链接插进了 PHP 示例）；把整段行内代码换成链接会吞掉 `::方法名()`（要「整段当链接文字」或另找一处）。 |
| 多应用的斜杠坑 | ① `RouteHookRewrite::assignRewrite()` 的**键要带前导 `/`**；② `controller_resource_prefix`：根应用 `'/res/'`、子应用 `'res/'`（前缀按 `'/'.controller_url_prefix.controller_resource_prefix` 拼，子应用的挂载前缀已带尾斜杠）；③ 相位名不是类名（子应用是 `:<name>`）。 |
| `ZAllDemoTest` 的字节长度比对 | 它把 demo 各路由输出长度与 `tests/data_for_tests/ZAllDemoTest.config.php` 硬比，`files` 路由会 dump **选项表**（`合计 N个`）+ 方法表 + 包含文件 + 调用栈行号，**源码一动就变**（把选项在 `$options` 与 `$hidden_options` 之间搬家、类改名同样会变）。注意那张表**只数根应用自己声明的选项**（`kernel_options + core_options + common_options + options`），组件自己的选项（比如 Logger 的 `path_log`）不进表——所以只改组件的装配方式/选项值不一定动这个长度。只有它红时：把期望值改成括号里的实际值（新 dump 存成 `tests/data_for_tests/ZAllDemoTest-<长度>.txt`，可 `diff` 新旧两份看差在哪），并在 config 的注释里记一句来历。**当前基线 10431**（`PhaseContainer` 的 dump 文案 `publics:` → `shared:` 少 1 字节）。 |
| 测试一律走 WSL | Windows 侧 PHP 没有 redis 扩展会假失败；`docs/scripts/*.sh` 也要在 WSL 跑。 |
| 别 `git add .` | 2026-09-26 起 `.gitignore` 已盖住编辑器/AI 助手的本地状态（`.obsidian/`、`.kimi/`、`.reasonix/`、`.vscode/`、`reasonix.toml`）和 `composer.lock`（库项目不锁依赖；docker 测试每次 `rm -f` 后按当前 PHP 版本重算），但**测试产物仍在未跟踪里**：`*/runtime/log_*.log`、`tests/data_for_tests/*/runtime/`、`ZAllDemoTest-*.txt`、`demo/runtime/dbtest.sqlite`，另有 `CODING_MEMO.md` 与作者特意保留的 `demo/config/lang-bak/`。提交前照样 `git status --short` 复核、按显式路径 `git add`；要动那些与本工作无关的未跟踪文件，先问作者。 |
| **Obsidian 保存时重排表格（或别的编辑器 / 并行的 AI 会话顺手格式化）** | 用 Obsidian 打开 `docs/zh/` 时保存会重排表格：按显示宽度补齐列宽、`\|---\|` 写成 `\| --- \|`，**还会补出多余的空列/空行**。对指南只是 diff 噪声，但 `reference/index.md` 的 `<!-- GEN:nav -->`、`reference/options.md` 的 `<!-- GEN:layers -->` 块内表格被重排会让 `gen-options-docs.php --check` 报 stale（生成器已忽略纯排版差异；空列属实质差异，要砍掉）。**当前 `docs/zh/.obsidian/` 里没装任何 community 插件**（三个库的 `community-plugins.json` 都是 `[]`），所以嫌疑在别的编辑器/并行会话。遇到这类改动先跑 `python3 docs/scripts/check-md-layout.py`：报 `layout-only` 的直接 `git checkout -- <file>` 丢掉，报 `CONTENT` 才看 diff。 |
| 中文文档不受 ASCII 限制 | `src/` 才必须纯 ASCII（改 `src/` 后跑 `docs/scripts/check-non-ascii.sh`，期望 `Total non-ASCII lines: 0`）；`docs/` 是中文，正常写。 |
| 子代理并行改章节的边界 | 适合「一章一文件、验收明确」的活；并行超过 3 个、或单个任务超过 4 章时失败率明显上升，**重试前先确认它对文件没有半途写入**。 |

## 6. 改一章时的标准动作

```powershell
$env:WSL_UTF8=1
# 1) 基线复核（改前改后各跑一次）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/check-doc-links.py docs/zh"   # 期望 broken: 0
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/find-unmentioned-classes.py"  # 期望「从没被链到: 0」
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php docs/scripts/gen-architecture-gv.php --check"  # 架构图源文件是否最新（动过 src/ 就重生成 + 重渲染 SVG）
python3 <tmp>/drift.py --all                     # reference 与源码一致性（脚本见参考手册维护指南 §5）

# 2) 改哪一章就守 §2 的约定：
#    · 章号用卷-章号（1-1 … 4-13），引用写「第 2-4 章」或范围「第 2-1–2-20 章」
#    · 类名第一次出现链到 ../reference/ 对应页（Base/Helper 用全限定名）
#    · 代码块里不插链接；示例仍只用 demo/、skeleton/、ZThirdDemo
#    · 不写旧文档的过时内容（旧文已删，只写当前事实）

# 3) 新增一章：a. 写 docs/zh/guide/<file>.md（H1 = 「# <卷-章号> <标题>」，模板见 §2）
#              b. 在 docs/zh/guide/index.md 对应卷的表里加一行（章号 + 链接 + 一句话）
#              c. 参考页里若有「见指南第 N 章」，按新号写
#              d. 跑 §4 的 ①②③③c；改过示例就补跑 ④

# 4) 删/并/拆章：总目录、「下一步」段、全库 `第 X-Y 章` 引用、参考页交叉引用一起改，改完按 §7 反查 0 处不一致

# 5) 收尾：只 add 相关路径（别 git add .）；**历史过程写在 commit message 里，不写进文档**
```

## 7. 章序与章号

- **形式**：卷-章号，单数字形式已全部废弃。范围：第一卷 `1-1`–`1-7`；第二卷 `2-1`–`2-20`；第三卷 `3-1`–`3-7`；第四卷 `4-1`–`4-13`；附录 `A`–`D`（术语表 / 片段 / 迁移 / FAQ）。
- **章号重排已完成（2026-09-26）**：`4-11`「过时与冷门的扩展类」并入 `Ext\*` 那一章时先留了缺号 + 总目录 `//TODO`；现在按 TODO 把原 `4-12`/`4-13`/`4-14` 顺次前移成 `4-11`/`4-12`/`4-13`（`impl-user.md` / `impl-admin.md` / `ext-classes.md`），全库 74 处 `4-1x` 章号（含 3 个 H1、链接文字、正文提及）、总目录表格与本文件的规范文字一并同步，卷四现在是连续的 `4-1`–`4-13`。**以后再并章/插章照同一条路走**：先把内容并进去、记 TODO + 缺号，集中重排时用「单遍回调映射」（`re.sub(r'(?<![0-9])4-1([0-9])(?![0-9])', cb, text)` 这种，前后加边界避免误伤 `104-111` 之类的行号），**别顺序 sed**（`4-12→4-11` 之后 `4-13→4-12` 会把它再搬一次）；顺次前移只改数字、文件名不带章号所以不用改名。判据见本节最后一条。
- **卷二的两个特殊点**：`2-1`（`layers.md`）是**指路页**，四层规范正文在 `1-3`（`project-structure.md`）——全库 33 处「第 2-1 章」引用都指向它，**别再改号**，改内容改 1-3；`2-3` 路由进阶与 `2-4` 路由钩子是一对（钩子排在进阶之后——作者指出「2-3 的前置是 2-4」），涉及路由的两章别对调回来。
- **`Ext\*` 的内容只在 4-13 讲**（作者裁定）：卷二/卷三只留「怎么用」的 1~3 行 + 指针，类名、`ext` 装配、选项与坑集中到 `ext-classes.md`；`Ext\RouteHookManager` 也归 4-13（卷二用 `Route::_()->addRouteHook()` / `dumpAllRouteHooksAsString()`）。往卷二加 `Ext\` 内容前先看这条。
- **唯一事实来源是总目录** [`guide/index.md`](guide/index.md)：章号、文件名、一句话都在那里。本文件只记「形式」与下面这些改号姿势。
- **改号的落地姿势**：单遍替换 + 回调映射（见 §5）；替换范围只对总目录的表格行与各章 H1 生效，别全库套用。
- **改完的判据**：① 扫 `docs/zh/guide/*.md` 里所有「链接文字带 `第 X-Y 章`」的引用，用「文件名 → 章号」表反查 → **0 处不一致**；② 总目录每行的章号与目标文件的 H1 对得上；③ `python3 docs/scripts/check-doc-links.py docs/zh` 仍 0 死链；④ 各章 H1 无重号、无缺号（卷四现在是连续的 `4-1`–`4-13`）。
