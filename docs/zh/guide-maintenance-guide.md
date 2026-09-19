# 用户指南重写 · 交接与维护指南

> 目标读者：接手「`docs/zh/guide/` 用户指南重写」这件事的 AI/工程师。
> 一句话任务：**把用户指南从「平铺清单」重写成「一页总目录 + 四卷 41 章 + 4 附录」**。
>
> - **做到哪了 / 还差什么** → 看 [进度 Checklist](guide-rewrite-checklist.md)（那份只管状态与勾选）
> - **怎么做** → 就是本文件（目标、约束、模板、校验命令、已知坑、起手动作）
> - 姊妹文件：[参考手册维护指南](reference-maintenance-guide.md)（`docs/zh/reference/` 的维护；其校验思路本文件直接复用）

---

## 1. 目标与设计

**梯度**：入门 → 单一应用 → 使用第三方应用 → 高级话题。

| 卷 | 读者状态 | 读完能做什么 | 章数 |
|---|---|---|---|
| 一 入门 | 没装过 | 跑起一个页面并上线 | 7 |
| 二 单一应用 | 会跑 | 独立写完一个业务应用（覆盖主流框架的全部常见主题） | 17 |
| 三 使用第三方应用 | 会写单应用 | 把外部应用挂进来、共享组件、覆盖它的内容 | 7 |
| 四 高级话题 | 会集成 | 改框架行为、调优、排错、维护文档 | 10 |

**硬约束**（作者已裁定，别自行放宽）：

1. **一页总目录** = `docs/zh/guide/index.md`；`docs/zh/index.md` 只做指路，不复制章表。
2. **示例只用现有代码**：`demo/`、`tests/data_for_tests/ZAllDemo`、`tests/data_for_tests/ZThirdDemo`；不新建示例工程（`ZThirdDemo` 是唯一获准新增的）。
3. **新写的章 ≤400 行**；改写章暂不限，但明显超长的要拆（见 Checklist 的 Q2）。
4. **附录**：原先指南侧的 `appendix-global-functions.md` 与 `appendix-options.md` **已删除**——全局函数参考由 `docs/zh/reference/Core-Functions.md` 承接，应用选项参考由 `reference/options.md` / `options-by-class.md` / `options-index.md` 承接；指南里只留指向参考手册的链接。
5. **不写**「升级与破坏性变更」章/附录。
6. **先不管 `skeleton/`**（不改它的文件；其 `agent-zh.md`/`RULES.md` 有失真内容，见 Checklist 的 Q3）。

## 2. 每章模板（全卷统一，照抄结构）

```
# <章号> <标题>

> 解决什么问题 · 前置章节 · 预计阅读时间
> （若本章有示例工程）一句话说明示例在哪、怎么跑

## 最小示例          ← 能跑；标明来源（demo/ 或 ZAllDemo 或 ZThirdDemo）
## 机制说明          ← 讲为什么；链到 reference 对应篇
## 常见写法          ← 3~5 个片段
## 常见错误          ← 表格：现象 / 原因 / 改法
## 下一步            ← 前后章 + 相关参考
```

- 术语只用 [附录 A 术语表](guide/appendix-glossary.md) 定下的叫法（应用 / 子应用 / 相位 / 挂载前缀 / 共享容器 / 覆盖 / Helper / 扩展）。
- 示例里统一用 `::_()`、`Helper::`、`__h()`；配置示例统一用 `MyProj` 作命名空间。
- 章内引用 reference 的链接必须**指向真实存在的文件**（用 §4 的脚本查）。

参考样板：[第 25 章](guide/advanced-phase.md)（机制讲清 + 坑位标注）与 [第 29 章](guide/overriding.md)（含「谁赢」的排查思路）。

## 3. 示例与验证资产

| 资产 | 服务哪几卷 | 怎么跑 |
|---|---|---|
| `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php` | 第三卷 25–31 | `wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` |
| `tests/data_for_tests/ZAllDemo` + `tests/ZAllDemoTest.php` | 第二卷（四层/视图/路由） | 同上换文件名；它是「起内置服务器 + curl 各路由比字节长度」的重型冒烟，改 `src/` 后长度会变（见 §5） |
| `demo/`（`public/` 多入口 + `src/System/AppWithAllOptions.php`） | 第一卷、第二卷 | `php -S 127.0.0.1:8080 -t demo/public` 后访问各入口 |

**约定：新写的章里每一段示例代码，都要能在上述资产里指出出处，或已在对话中实跑过。** 第三卷的 7 章就是这么做的——每条章内结论都对应 `ZThirdDemoTest` 里的一条断言。

## 4. 校验命令（每写完一章/一卷就跑）

```powershell
$env:WSL_UTF8=1      # 一次即可，避免 wsl 输出乱码

# ① 全仓 md 相对链接是否都存在（含新章、TOC、迁入页）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 scripts/check-doc-links.py docs"

# ② 新文件编码（GBK 会被报出来）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 - <<'EOF'
import glob,io
bad=[]
for p in glob.glob('docs/zh/guide/*.md')+glob.glob('docs/zh/reference/*.md'):
    try: io.open(p,encoding='utf-8').read()
    except UnicodeDecodeError: bad.append(p)
print('non-utf8:', bad or 'none')
EOF"

# ③ 新写的章 ≤400 行
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && wc -l docs/zh/guide/*.md | sort -n | tail -20"

# ④ 改过 src/ 或 tests/ 时：跑相关单测；改过示例就重跑示例测试
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"

# ⑤ 收尾（全量，约 5.5 分钟，建议后台跑）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage"
```

当前基线：全量 `OK (92 tests, 556 assertions)`；`docs/zh` 相对链接 **0 坏链**；`docs/` 下只有归档目录 `docs/old/` 还有历史坏链（不属本轮范围）。校验结果请登记到 [Checklist](guide-rewrite-checklist.md) 的「每轮收尾自检」。

`scripts/check-doc-links.py`（本轮新增；扫 `docs/**/*.md` 的相对链接，忽略外链与锚点，恒退出 0——读打印的 `broken: N`）：

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
| **未写的章不要挂链接** | TOC 里未落稿的章用 `⏳ + 纯文本`，写完后替换成链接；否则链接校验一直红。（本轮这么做，`docs/zh` 才能保持 0 坏链） |
| 旧章里的 API 可能早就不存在 | 实测：`advanced-phase.md` 有 3 处 `App::Root()->getOverridingClass()`（源码里没有）；`helper.md` 的 `assignRewrite('article/123', …)` 少了前导 `/` 因而永不命中；`Configer` 读 `config/<名>.php` 而不是 `<名>.config.php`。**改写前先核对源码。** |
| 多应用的斜杠坑 | ① `RouteHookRewrite::assignRewrite()` 的**键要带前导 `/`**；② `controller_resource_prefix`：根应用 `'/res/'`、子应用 `'res/'`（前缀按 `'/'.controller_url_prefix.controller_resource_prefix` 拼，子应用的挂载前缀已带尾斜杠）；③ 相位名不是类名（子应用是 `:<name>`）。 |
| `ZAllDemoTest` 的字节长度比对 | 它把 demo 各路由输出长度与 `tests/data_for_tests/ZAllDemoTest.config.php` 硬比，`files` 路由会 dump **选项表**（`合计 N个`）+ 方法表 + 包含文件 + 调用栈行号，**源码一动就变**（把选项在 `$options` 与 `$hidden_options` 之间搬家同样会变）。只有它红时：把期望值改成括号里的实际值（新 dump 会存成 `tests/data_for_tests/ZAllDemoTest-<长度>.txt`，可 `diff` 新旧两份看差在哪）；先确认这轮没碰 `src/`，再用 `git stash push -- src` 验证是不是本来就红。**当前基线 10360**（第一卷收尾时对齐）。 |
| 测试一律走 WSL | Windows 侧 PHP 没有 redis 扩展会假失败；`scripts/*.sh` 也要在 WSL 跑。 |
| 别 `git add .` | 仓库有未跟踪的 `.obsidian/`（`docs/zh/guide/` 与 `docs/zh/reference/` 各一个）、测试产物 `*/log_*.log`、`ZAllDemoTest-*.txt` 等。提交前 `git status --short` 复核。 |
| 中文文档不受 ASCII 限制 | `src/` 才必须纯 ASCII（改 `src/` 后跑 `scripts/check-non-ascii.sh`）；`docs/` 是中文，正常写。 |

## 6. 下一轮的起手动作

```powershell
$env:WSL_UTF8=1
# 1) 基线复核：链接是否仍 0 坏链；reference 是否仍 0 不一致（这轮没动 reference 可跳过）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 scripts/check-doc-links.py docs"
python3 <tmp>/drift.py --all

# 2) 按 Checklist 的 M2 顺序推进（章号已重排：第二卷 8–24，中间件并入 17，见 Checklist 第二卷小节）：
#    a. 先拆薄两份长文：layers.md → 拆出 10 controllers.md / 11 views.md，自己只留四层规范（成为第 8 章）
#                        lifecycle.md → 拆出 16 的会话节、18 的异常节、19 events.md，自己只留生命周期 + 钩子（成为第 17 章）
#    b. 再逐个改写：8 layers（四层规范）→ 9 routing → 12 database → 13 model → 14 helper（含修示例）
#                  → 15 validator → 16 external-auth（含修旧键名）→ 18 exception → 22 cli → 23 testing
#    c. 最后补新章：10 controllers / 11 views / 17 lifecycle（拆薄后重写）/ 19 events / 20 cache
#                  / 21 i18n / 24 security-performance（各 ≤400 行）
#    d. 第 17 章还要收两个附节：兼容性扩展（Ext\MyMiddlewareManager，含短路无效的坑）与钩子链
#       （Route::addRouteHook 六位置、内置钩子位置清单、RouteHookManager、Ext\HookChain）
# 3) 每写完一章：跑 §4 的 ①②③；改过示例就补跑 ④
# 4) 一卷结束：勾 Checklist、登记校验结果、在对话里报一次
```

## 7. 本轮（第三卷）交付记录

- 示例工程：`tests/data_for_tests/ZThirdDemo`（主应用 `src/` + 被挂的 `third/` 第三方应用；四个覆盖点：视图 `view/shop/index.php`、配置 `config/shop/greet.php`、资源 `res/shop/third.css`、控制器 `src/Override/ShopControllerOverride.php`；另有事件总线与两种跨相位调用）。
- 测试：`tests/ZThirdDemoTest.php`，**36 断言**；一次 `init` 多次 `serve()`，逐条断言「有覆盖」与「没覆盖时回落子应用自己那份」。
- 指南：一页总目录 `docs/zh/guide/index.md`、附录 A `appendix-glossary.md`、改写 25、新写 26–31（行数 174/125/120/134/140/122/206，均 ≤400）。
- 其他：`docs/zh/index.md` 瘦身成指路页；顺手修掉 `docs/zh/guide/routing.md`(3 处) 与 `external-auth.md`(4 处) 的相对路径坏链；新增 `scripts/check-doc-links.py`。

## 8. 本轮（第一卷）交付记录

- 章节（总目录 `docs/zh/guide/index.md` 第 1–7 行）：`intro.md`(108)、`install.md`(161)、`project-structure.md`(136)、`quickstart.md`(226)、`configuration.md`(141)、`debugging.md`(122)、`deployment.md`(164) —— 新写 2 篇（intro / project-structure），改写 5 篇；行数全部 ≤400。
- 示例：不新造工程，全部挂在现成的 `demo/` 上（`quickstart.md` 的便签列表走五层；`deployment.md` 的内置服务器写法实测过 `php -S 127.0.0.1:8080 -t demo/public demo/public/router.php` 一类的 router 要点）。顺手修掉 `demo/public/helloworld.php` 里 `catch (\Thowable $e)` 的旧文档错字（`catch` 不触发自动加载 ⇒ 该 catch 永不命中）。
- 校验：`docs/zh` 站内链接 **1005 条 0 死链**；`docs/zh` 153 篇 md 逐字节 UTF-8；全量 `php vendor/bin/phpunit --no-coverage` → **`OK (92 tests, 556 assertions)`**（其中 `ZAllDemoTest` 的 `files` 期望长度 10429 → **10360**，是并行会话的选项表重构所致，已在交接指南第 7 节记录判断依据）。
- 第四卷/附录与第二卷仍未开工；未决问题（Q1–Q4）见 `guide-rewrite-checklist.md` 末尾。

## 9. 第二卷章序与章号重排（作者裁定，M2 开工前生效）

**第一次裁定（重排顺序）**：第二卷不再按「生命周期打头、四层架构靠后」，改成**先立规范 → 再走请求路径 → 再补横切能力 → 最后框架机制与进阶**。最终结果：

| 新 | 标题 | 文件 | 旧编号 |
|---|---|---|---|
| 8 | 四层架构与调用规范 | `layers.md` | 14 |
| 9 | 路由进阶 | `routing.md` | 9 |
| 10 | 控制器 | `controllers.md`（待写） | 10 |
| 11 | 视图与模板 | `views.md`（待写） | 11 |
| 12 | 数据库 | `database.md` | 12 |
| 13 | 模型层 | `model.md` | 13 |
| 14 | Helper 与全局函数 | `helper.md` | 15 |
| 15 | 表单与数据验证 | `validator.md` | 16 |
| 16 | 会话与用户/管理员体系 | `external-auth.md` | 17 |
| 17 | 请求生命周期与钩子点（含兼容扩展：洋葱中间件 + 钩子链） | `lifecycle.md` | 8 |
| 18 | 异常与错误处理 | `exception.md` | （原 19） |
| 19 | 事件系统 | `events.md`（待写） | （原 20） |
| 20 | 缓存与 Redis | `cache.md`（待写） | （原 21） |
| 21 | 国际化与文案 | `i18n.md`（待写） | （原 22） |
| 22 | 命令行与定时任务 | `cli.md` | （原 23） |
| 23 | 测试 | `testing.md` | （原 24） |
| 24 | 安全与性能清单 | `security-performance.md`（待写） | （原 25） |

（第 18 章「中间件与钩子链」已撤销，见下。）

**第二次裁定（撤销第 18 章）**：作者指出「中间件毕竟不是 DuckPHP 主推的东西，只是为了兼容而附带的扩展」，于是：
- 第 18 章「中间件与钩子链」**不单独成章**，其内容并入第 17 章「请求生命周期与钩子点」——路由钩子三位置/短路语义/内置钩子清单/`RouteHookManager`/`Ext\HookChain` 讲机制，`Ext\MyMiddlewareManager` 只作为「兼容性扩展」一节交代；
- 中间件的**短路语义按现状写、不改源码**（作者裁定 B）：实测中间件里不调 `$next` 就 return 时，返回值被丢弃、`Route::run()` 还会再跑一次默认回调 ⇒ **控制器照样执行**；这一条作为坑写进第 17 章，并给正解「要真拦截 → 用路由钩子并返回真值」。
- 原 19–25 顺次前移为 18–24；第三卷 26–32 → **25–31**；第四卷 33–42 → **32–41**；全书 **41 章** + 4 附录（`guide-maintenance-guide` §1 表、`guide-rewrite-checklist` 的 M1/M2/M4 与各卷清单、总目录三张表都要跟着改）。

**落地时改了什么**（两次共）：
- 第一次：总目录、checklist 第二卷小节、本文档 §6/§9，以及 10 个文件的 16 处 `第 N 章` 交叉引用（旧 8/14/15/16/17 之间的置换）。
- 第二次：`第 N 章`（N ≥ 19）全体 −1 共 17 个文件；第三卷 7 篇的 H1 章号 −1（`# 26 → # 25` … `# 32 → # 31`）；总目录删掉中间件那一行并重排三张表；checklist 与两份维护指南里的章数/范围（`42 章`→`41 章`、`26–32`→`25–31`、`33–42`→`32–41`、`8–25`→`8–24`、`18–25`→`18–24`）。
- **两次都用了单遍替换**（Python `re.subn` + 回调映射，绝不顺序 `sed`——那会链式误改，比如 `14→8` 之后 `8→17` 又把它改走）。

**校验方式**（下次改章号照抄）：脚本扫 `docs/zh/guide/*.md` 里所有「章号 + 链接」形式的引用（链接文字里带 `第 N 章`、目标指向某篇），用「文件名 → 章号」表反查 → **0 处不一致**；总目录解析出的每一行章号也与文件名对得上；另外 `python3 scripts/check-doc-links.py docs/zh` 仍 0 死链。

## 10. 本轮（第二卷）交付记录

- 章节（17 篇，全部 ≤400 行，总目录 `docs/zh/guide/index.md` 第二卷表）：
  `layers.md`(226)、`routing.md`(221)、`controllers.md`(204)、`views.md`(187)、`database.md`(242)、`model.md`(259)、`helper.md`(177)、`validator.md`(180)、`external-auth.md`(228)、`lifecycle.md`(293)、`exception.md`(207)、`events.md`(127)、`cache.md`(137)、`i18n.md`(123)、`cli.md`(217)、`testing.md`(151)、`security-performance.md`(181)。
  其中**新写 6 篇**（controllers / views / events / cache / i18n / security-performance），其余 11 篇为改写；`layers.md` 由 581 行拆成「四层规范」并把控制器/视图内容交给 10/11 两章。
- 分工与执行：先由 5 个子代理并行改写（第 15/16/18/19/20/21 章共 6 篇由子代理完成），**其余 11 篇因子代理连续失败改由主代理亲自写**（`routing`/`controllers`/`views`/`database`/`model`/`helper`/`lifecycle` 及 `cli`/`testing`/`security-performance`/`layers`）。教训：子代理适合「一章一文件、明确验收」的活；并行超过 3 个或单个任务超过 4 章时失败率明显上升，**重试前先确认它对文件没有半途写入**。
- 示例策略（守硬约束 2，不新建示例工程）：全部指向现成资产 —— `demo/public/demo.php`（单文件五层 + 路由 + 函数式视图）、`demo/public/dbtest.php`（模型/分页/CRUD 全链路）、`demo/src/*`、`tests/data_for_tests/ZAllDemo`、`tests/data_for_tests/ZThirdDemo`。
- **本轮实测过的命令**（写进章首「怎么跑」）：
  - `php demo/cli.php help|routes|version|DbTestApp:version` —— 用来确认内置七命令、命令组前缀与相位的关系；
  - 临时脚本（跑完即删，未入库）验证 `regConsoleCommand()` + `command_xxx()` + `@command_desc` 能让命令出现在 help 里，以及 `--k=v` 解析成 `['k'=>'v']`、位置参数在 `['--']`；
  - `tests/Ext/MyMiddlewareManagerTest.php` —— 验证洋葱顺序与「中间件短路无效」那条坑。
- 校验：`docs/zh` 站内链接 **1281 条 0 死链**；全量 `php vendor/bin/phpunit --no-coverage` → `OK (92 tests, 556 assertions)`；17 章 H1 与总目录章号逐一对齐（脚本反查）。
- 顺带发现、**未改源码**（留给作者裁定/后续轮次）：
  1. `demo/src/Controller/Commands.php` 里的 `use DuckPhp\Foundation\CommonCommandTrait;` —— 该 trait 在当前 `src/` 中**不存在**（第 22 章已注明"别照抄这一行"）；
  2. `demo/public/dbtest.php` 的 `cli_command_prefix` 与 `bin/duckphp` 的 `cli_command_classes` 在 `src/` 里**没有任何读取点**（死选项）；
  3. `demo/src/Model/CrossModelEx.php` 只有 `foo()` 空壳，名字却暗示"跨库模型"（第 13 章已写明「不要从名字推断用法」）；
  4. `demo/src/Business/CommonService.php`、`demo/src/Controller/CommonAction.php` 也都是空壳样板（第 8 章已按"占位样板"表述，没有把它们说成现成功能）。

## 11. 本轮（第四卷 + 附录 B/C/D）交付记录

- 章节（10 篇，全部 ≤400 行）：`container-phases.md`(126)、`custom-component.md`(161)、`replace-behavior.md`(181)、`embed.md`(153)、`http-server.md`(164)、`multi-entry.md`(152)、`coverage.md`(154)、`doc-maintenance.md`(158)、`troubleshooting.md`(206)、`design-notes.md`(80)。
- 附录：`appendix-snippets.md`(288)、`appendix-migration.md`(123)、`appendix-faq.md`(117)。**全书 41 章 + 4 附录至此全部落稿**。
- 内容来源与吸收：
  - 第 32/33 章吸收 `architecture.md`（`PhaseContainer` 分桶/查找、组件初始化模板、`EXT_*` 语义）与 `components.md`（组件默认启用 vs 扩展默认关闭）；
  - 第 17 章吸收 `architecture.md` 的时序；第 8 章吸收其四层部分 ⇒ `architecture.md` / `components.md` 已是"可删"状态（删除动作归 M5）；
  - **纠错**：`architecture.md` 里写的公共桶名 `@public@` 是错的，源码是 `#public`（`KernelTrait`）；相位名也不是类名（是 `:<name>`）——第 32 章按源码写，M5 删旧文时不会被带偏。
- 分工与执行：3 个子代理并行（32/33、35/37 各成一个；34/36 的那个跑了 25 分钟无产出，被**主动中断**后由主代理接手写），其余 7 篇（34/36/38/39/40/41 + 附录 B/C/D）由主代理完成。教训补充：子代理长时间零产出时，**先 interrupt 再自己写**，别让它和你抢同一个文件。
- 校验：`docs/zh` 站内链接 **1613 条 0 死链**；10 章 H1 与总目录逐一对齐；全量 `php vendor/bin/phpunit --no-coverage` → `OK (92 tests, 556 assertions)`。
- 本轮"如实标注"的三处（避免把示意当现成功能）：第 33 章的 `HelloBanner` 组件、附录 B 里标 ⚠️ 的 HTTPS/CSRF/上传片段、第 34 章"谁赢"优先级表（属经验总结而非源码常量）。
- **M5 收尾见下一节。**

## 12. M5 收尾记录（本轮完成：指南全书交付）

- **全局函数参考不新建页**（作者裁定）：由现成的 `docs/zh/reference/Core-Functions.md`（208 行，含 17 个函数的全集签名表与分组讲解）承接。
- **删除的被吸收文件**（都还在 git 历史里，需要时 `git show` / `git checkout` 取回）：
  - `docs/zh/guide/architecture.md`（532 行）—— 内容分别进了第 32 章（容器/相位/组件初始化）、第 17 章（时序）、第 8 章（四层）；
  - `docs/zh/guide/components.md` —— 进了第 33 章（组件与扩展）；
  - `docs/zh/guide/appendix-global-functions.md`、`docs/zh/guide/appendix-options.md` —— 由参考手册的 `Core-Functions.md` 与 `options*.md` 承接。
- **链接改向**（删文件前先改，保证校验始终 0 死链）：`helper.md`、`layers.md` 里的「全局函数参考」→ `../reference/Core-Functions.md`；`index.md` 底部那行改成三个精确链接（Core-Functions / options / options-by-class / options-index）。`configuration.md` 里本来没有附录链接，无需改。
- **`docs/en/` 不受影响**：英文侧有自己同名的副本文件（`docs/en/guide/appendix-options.md` 等），本次只删了 `docs/zh` 侧的。
- 校验：`docs/zh` 站内链接 **1622 条 0 死链**；`docs/zh` 全 UTF-8；`docs/zh/guide/` 现在正好是 `index.md` + 41 章 + 4 附录。
- **仍然留着的两件事**（都不是文档问题，等作者定）：Checklist 的 **Q3**（`skeleton/` 里 `agent-zh.md`/`RULES.md` 的失真内容）与 **Q4**（`DuckPhp::_Show()` 里那个没赋值的死表达式）。

## 13. 收尾：Q3 / Q4 修复记录

**Q4——`_Show()` 的死表达式：源码其实已经修好，本轮只补了防回归测试。**
- `DuckPhp::_Show()` 里那句没赋值的 `$view === '' ? Route::_()->getRouteCallingPath() : $view;` 已在提交 `9635a77b`（"文档推进"）中删除；现在 `App::_Show()` 写的是 `$view = ($view === '') ? Route::_()->getRouteCallingPath() : $view;`（正确赋值），`GlobalUser::_Show()`/`GlobalAdmin::_Show()` 同样都已赋值。
- 在 `tests/DuckPhpTest.php` 的 `_Show()` 三分支测试后加了一条断言：把 `Route::_()->calling_path` 设为 `block`，再调 `DuckPhp::_()->_Show($data, '')`，必须渲染出 `view/block.php` 的内容。**把 bug 改回去跑一遍确认它会红**（报 `ValueError: Path cannot be empty`），再还原——这是本仓库对回归测试的硬要求。

**Q3——`skeleton/`（模板工程）的失真内容：已修，并顺带修掉一个更严重的 bug。**
- `skeleton/agent-zh.md`：`'class_user' => MyUserAction::class`（以及注释里的 `class_admin`）是**已不存在的旧选项**；改为真实写法——整体替换用 `user_provider`/`admin_provider`（框架会把实例包成相位代理装进 `GlobalUser::_()`/`GlobalAdmin::_()`），逐项接入用 `user_callback_for_*`，并补上真实接口名（`UserActionInterface`/`UserServiceInterface`/`UserSessionInterface`/`UserLoginActionInterface`/`UserLoginServiceInterface`）。
- `skeleton/RULES.md`：方法前缀默认值从错的 `action_` 改成 **空串**（并写明 DuckPHP 1.3.6 起由 `action_` 改为空、本骨架的 `App.php` 里仍配着 `'action_'`）；`/Main/index` 那条示例改为「默认会被拒（E009），要允许得置 `controller_welcome_class_visible => true`」，关键约定表同步补一行。
- `skeleton/src/System/App.php`：`cmd` 选项的注释示例 `[CommandAction::class]` → `[CommandAction::class => true]`（真实形态是「类名 => 方法前缀或 `true`」；`Console::getCommandCallback()` 是按 `$class => $method_prefix` 遍历的，列表形式会失效）。
- **顺带查出并修掉的真 bug**：`skeleton/src/System/ProjectException.php` 与 `demo/src/System/ProjectException.php` 都只 `use ExceptionTrait`（该 trait 只带来 `ThrowOnTrait`），**没有 `extends \Exception`** ⇒ `Helper::BusinessThrowOn()` 抛它时会"无法抛出非 Throwable"致命错误。两处都改成 `class ProjectException extends \Exception`。
- 回归测试：`tests/Foundation/ExceptionTraitTest.php` 新增 `testProjectExceptionClassesAreThrowable()`，直接加载这两个真实文件并断言 `is_subclass_of(..., \Throwable::class)` 且能真的 `throw`。**把 `extends \Exception` 去掉跑一遍，测试会红**（已实测），再还原。
- 校验：`src/` 未被改动（`git diff -- src` 为空）；`bash scripts/check-non-ascii.sh` → `Total non-ASCII lines: 0`；全量测试 **`OK (93 tests, 565 assertions)`**（比原基线 `92/556` 多 1 个测试方法 + 9 个断言，即这两条回归测试）。
