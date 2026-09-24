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
| 二 单一应用 | 会跑 | 独立写完一个业务应用（覆盖主流框架的全部常见主题） | 20（含 2-2/2-3 生命周期与路由钩子、2-19/2-20「使用用户/管理员系统」） |
| 三 使用第三方应用 | 会写单应用 | 把外部应用挂进来、共享组件、覆盖它的内容 | 7 |
| 四 高级话题 | 会集成 | 改框架行为、调优、排错、维护文档、自己实现用户/管理员体系 | 13（含 4-12/4-13「实现用户/管理员系统」） |

**硬约束**（作者已裁定，别自行放宽）：

1. **一页总目录** = `docs/zh/guide/index.md`；`docs/zh/index.md` 只做指路，不复制章表。
2. **示例只用现有代码**：`demo/`、`tests/data_for_tests/ZAllDemo`、`tests/data_for_tests/ZThirdDemo`；不新建示例工程（`ZThirdDemo` 是唯一获准新增的）。
3. **新写的章 ≤400 行**；改写章暂不限，但明显超长的要拆（见 Checklist 的 Q2）。
4. **附录**：原先指南侧的 `appendix-global-functions.md` 与 `appendix-options.md` **已删除**——全局函数参考由 `docs/zh/reference/Core-Functions.md` 承接，应用选项参考由 `reference/options.md` / `options-by-class.md` / `options-index.md` 承接；指南里只留指向参考手册的链接。
5. **不写**「升级与破坏性变更」章/附录。
6. **章号一律用「卷-章」号**：`1-1`–`1-7`、`2-1`–`2-20`、`3-1`–`3-7`、`4-1`–`4-13`（附录仍是 A/B/C/D）。**不要再出现单数字章号**（例如写成「数字 + 章」的样子）。
7. **类名第一次出现时，链接到参考手册对应页**（例如正文里写 `Route::_()` 时，首次出现处链成 `[Route](reference/Core-Route.md)`；`Base`/`Helper` 有多个同名页，必须用全限定名形式）。
8. **不写旧文档的过时内容**：`architecture.md` / `components.md` / 两篇指南附录都已删除，指南里不要再出现「旧文档里是这么写的」「已废弃的 X」之类表述——只写当前事实，必要时直接给正确写法。
9. `skeleton/` 的失真内容已在 Q3 轮修掉（`class_user`→`user_provider`、方法前缀默认值、`ProjectException extends \Exception` 等）；模板工程改动同样要跑测试（`tests/Foundation/ExceptionTraitTest.php` 有防回归断言）。
10. **「怎么用」与「怎么实现」分章**（第 6 轮作者裁定）：卷二末尾的 2-18/2-19 只讲**调用方怎么用**（Helper 入口、未登录表现、视图开关）；「接入实现」（三个实现 + 选项 + `ext` 挂载）在第四卷的 4-12/4-13。以后写涉及用户/管理员的内容按这条分家，别把选项表塞回 2-18/2-19。

## 2. 每章模板（全卷统一，照抄结构）

```
# <卷-章号> <标题>

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
- **代码块里不要插链接**（改号/加链脚本要跳过围栏代码块，否则示例没法直接复制）；类名链接放在正文或行内代码里。
- 章号引用写法：`第 2-4 章`、范围写 `第 2-1–2-20 章`；链接文字带章号时目标必须与章号一致（§4 有反查脚本）。

参考样板：[第 3-1 章](guide/advanced-phase.md)（机制讲清 + 坑位标注）与 [第 3-5 章](guide/overriding.md)（含「谁赢」的排查思路）。

## 3. 示例与验证资产

| 资产 | 服务哪几卷 | 怎么跑 |
|---|---|---|
| `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php` | 第三卷 3-1–3-7 | `wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` |
| `tests/data_for_tests/ZAllDemo` + `tests/ZAllDemoTest.php` | 第二卷（四层/视图/路由） | 同上换文件名；它是「起内置服务器 + curl 各路由比字节长度」的重型冒烟，改 `src/` 后长度会变（见 §5） |
| `demo/`（`public/` 多入口 + `src/System/AppWithAllOptions.php`） | 第一卷、第二卷 | `php -S 127.0.0.1:8080 -t demo/public` 后访问各入口 |
| `docs/scripts/`（8 个文档工具） | 全卷校验/生成 | `python3 docs/scripts/check-doc-links.py docs/zh`、`python3 docs/scripts/find-unmentioned-classes.py`、`bash docs/scripts/check-non-ascii.sh` 等；**脚本随文档一起提交**，都从仓库根目录跑 |

**约定：新写的章里每一段示例代码，都要能在上述资产里指出出处，或已在对话中实跑过。** 第三卷的 7 章就是这么做的——每条章内结论都对应 `ZThirdDemoTest` 里的一条断言。

## 4. 校验命令（每写完一章/一卷就跑）

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

# ③ 新写的章 ≤400 行
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && wc -l docs/zh/guide/*.md | sort -n | tail -20"

# ④ 改过 src/ 或 tests/ 时：跑相关单测；改过示例就重跑示例测试
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"

# ⑤ 收尾（全量，约 5.5 分钟，建议后台跑）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage"
```

当前基线：全量 `OK (92 tests, 556 assertions)`；`docs/zh` 相对链接 **0 坏链**；`docs/` 下只有归档目录 `docs/old/` 还有历史坏链（不属本轮范围）。校验结果请登记到 [Checklist](guide-rewrite-checklist.md) 的「每轮收尾自检」。

`docs/scripts/check-doc-links.py`（本轮新增；扫 `docs/**/*.md` 的相对链接，忽略外链与锚点，恒退出 0——读打印的 `broken: N`）：

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
| 测试一律走 WSL | Windows 侧 PHP 没有 redis 扩展会假失败；`docs/scripts/*.sh` 也要在 WSL 跑。 |
| 别 `git add .` | 仓库有未跟踪的 `.obsidian/`（`docs/zh/guide/` 与 `docs/zh/reference/` 各一个）、测试产物 `*/log_*.log`、`ZAllDemoTest-*.txt` 等。提交前 `git status --short` 复核。 |
| **Obsidian 保存时重排表格** | 用 Obsidian（Advanced Tables 类插件）打开 `docs/zh/` 时，保存会重排表格：按显示宽度补齐列宽、把 `\|---\|` 写成 `\| --- \|`，**还会补出多余的空列/空行**。对指南（手写章）只是 diff 噪声，**不影响内容**；但 `reference/index.md` 的 `<!-- GEN:nav -->`、`reference/options.md` 的 `<!-- GEN:layers -->` 块内表格被重排会让 `gen-options-docs.php --check` 报 stale（现已改为忽略排版差异，`layout-only differences ignored`；多出来的空列属实质差异，要砍掉）。详见[参考手册维护指南](reference-maintenance-guide.md) 的陷阱表。 |
| 中文文档不受 ASCII 限制 | `src/` 才必须纯 ASCII（改 `src/` 后跑 `docs/scripts/check-non-ascii.sh`）；`docs/` 是中文，正常写。 |

## 6. 下一轮的起手动作

> 全书 47 章 + 4 附录已交付（重写收尾时是 41 章；之后 2-9 拆出用户/管理员两章、M8 加了 4-11、M12 加了 4-12/4-13、M13 又把原 2-10 拆成 2-2/2-3），所以这里的「下一轮」指的是**增量维护**（加新章、改现有章、跟源码同步）。

```powershell
$env:WSL_UTF8=1
# 1) 基线复核（改文档前后各跑一次）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/check-doc-links.py docs/zh"   # 期望 broken: 0
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && python3 docs/scripts/find-unmentioned-classes.py"  # 期望「从没被链到: 0」
python3 <tmp>/drift.py --all                                                                        # reference 与源码一致性

# 2) 改哪一章就守 §2 的四条约定：
#    · 章号用卷-章号（1-1 … 4-13），引用写「第 2-4 章」或范围「第 2-1–2-20 章」
#    · 类名第一次出现链到 ../reference/ 对应页（Base/Helper 用全限定名）
#    · 代码块里不插链接；示例仍只用 demo/、ZAllDemo、ZThirdDemo
#    · 不写旧文档的过时内容（旧文已删，只写当前事实）

# 3) 新增一章时的完整动作：
#    a. 写 docs/zh/guide/<file>.md（H1 = 「# <卷-章号> <标题>」，模板见 §2）
#    b. 在 docs/zh/guide/index.md 对应卷的表里加一行（章号 + 链接 + 一句话）
#    c. 在 docs/zh/guide-rewrite-checklist.md 登记
#    d. 跑 §4 的 ①②③；改过示例就补跑 ④

# 4) 改完收尾：勾 Checklist、把校验结果写进本节与 reference-maintenance-guide 的轮次记录
```

## 7. 本轮（第三卷）交付记录

- 示例工程：`tests/data_for_tests/ZThirdDemo`（主应用 `src/` + 被挂的 `third/` 第三方应用；四个覆盖点：视图 `view/shop/index.php`、配置 `config/shop/greet.php`、资源 `res/shop/third.css`、控制器 `src/Override/ShopControllerOverride.php`；另有事件总线与两种跨相位调用）。
- 测试：`tests/ZThirdDemoTest.php`，**36 断言**；一次 `init` 多次 `serve()`，逐条断言「有覆盖」与「没覆盖时回落子应用自己那份」。
- 指南：一页总目录 `docs/zh/guide/index.md`、附录 A `appendix-glossary.md`、改写 25、新写 26–31（行数 174/125/120/134/140/122/206，均 ≤400）。
- 其他：`docs/zh/index.md` 瘦身成指路页；顺手修掉 `docs/zh/guide/routing.md`(3 处) 与 `external-auth.md`(4 处) 的相对路径坏链；新增 `docs/scripts/check-doc-links.py`。

## 8. 本轮（第一卷）交付记录

- 章节（总目录 `docs/zh/guide/index.md` 第 1–7 行）：`intro.md`(108)、`install.md`(161)、`project-structure.md`(136)、`quickstart.md`(226)、`configuration.md`(141)、`debugging.md`(122)、`deployment.md`(164) —— 新写 2 篇（intro / project-structure），改写 5 篇；行数全部 ≤400。
- 示例：不新造工程，全部挂在现成的 `demo/` 上（`quickstart.md` 的便签列表走五层；`deployment.md` 的内置服务器写法实测过 `php -S 127.0.0.1:8080 -t demo/public demo/public/router.php` 一类的 router 要点）。顺手修掉 `demo/public/helloworld.php` 里 `catch (\Thowable $e)` 的旧文档错字（`catch` 不触发自动加载 ⇒ 该 catch 永不命中）。
- 校验：`docs/zh` 站内链接 **1005 条 0 死链**；`docs/zh` 153 篇 md 逐字节 UTF-8；全量 `php vendor/bin/phpunit --no-coverage` → **`OK (92 tests, 556 assertions)`**（其中 `ZAllDemoTest` 的 `files` 期望长度 10429 → **10360**，是并行会话的选项表重构所致，已在交接指南第 7 节记录判断依据）。
- 第四卷/附录与第二卷仍未开工；未决问题（Q1–Q4）见 `guide-rewrite-checklist.md` 末尾。

## 9. 第二卷章序与章号重排（作者裁定）

> **章号形式**：作者后来要求**不再用单一数字，统一改成「卷-章」号**（例：早先的单数字 11 → 现在的 **2-6**）。现在的对应关系：
> 第一卷 `1-1`–`1-7`；第二卷 `2-1`–`2-20`（M13 把原 2-10 拆成 2-2 请求生命周期 + 2-3 路由钩子，其后各章顺次后移）；第三卷 `3-1`–`3-7`；第四卷 `4-1`–`4-13`（4-12/4-13 是第 6 轮新加的两章「实现用户/管理员系统」）；附录仍是 A/B/C/D。**单数字形式已全部废弃**，文档里不要再写成「数字 + 章」的样子（历史叙述里提旧编号时也只写数字）。

> ⚠️ **读历史记录注意**：本文件 §10 及之前几轮的交付记录里，章号是**当时**的编号（第 5–6 轮的 `2-1`–`2-19` 与 M13 后的 `2-1`–`2-20` 不是一回事），看老记录时以本节的表为准回推。

**第一次裁定（重排顺序）**：第二卷不再按「生命周期打头、四层架构靠后」，改成**先立规范 → 再走请求路径 → 再补横切能力 → 最后框架机制与进阶**。最终结果（下表用现在的卷-章号；「最初」列是被废除的单数字）：

| 章（卷-章） | 标题 | 文件 | 最初编号 |
|---|---|---|---|
| 2-1 | 四层架构与调用规范 | `layers.md` | 14 |
| 2-2 | 请求生命周期（M13 从原 2-10 拆出，并补「内置组件清单」） | `lifecycle.md` | — |
| 2-3 | 路由钩子（M13 从原 2-10 拆出并改名；含洋葱中间件与钩子链两节） | `route-hooks.md` | — |
| 2-4 | 路由进阶 | `routing.md` | 9 |
| 2-5 | 控制器 | `controllers.md` | 10 |
| 2-6 | 视图与模板 | `views.md` | 11 |
| 2-7 | 数据库 | `database.md` | 12 |
| 2-8 | 模型层 | `model.md` | 13 |
| 2-9 | Helper 与全局函数 | `helper.md` | 15 |
| 2-10 | 表单与数据验证 | `validator.md` | 16 |
| 2-11 | 会话 | `session.md` | 17 |
| 2-12 | 异常与错误处理 | `exception.md` | 19 |
| 2-13 | 事件系统 | `events.md` | 20 |
| 2-14 | 缓存与 Redis | `cache.md` | 21 |
| 2-15 | 国际化与文案 | `i18n.md` | 22 |
| 2-16 | 命令行与定时任务 | `cli.md` | 23 |
| 2-17 | 测试 | `testing.md` | 24 |
| 2-18 | 安全与性能清单 | `security-performance.md` | 25 |
| 2-19 | 使用用户系统（原 2-9 拆出，放在卷二末尾；M12 起只讲用法） | `user.md` | — |
| 2-20 | 使用管理员系统（同上；实现见 4-13） | `admin.md` | — |

**第二次裁定（撤销「中间件与钩子链」这一章）**：作者指出「中间件毕竟不是 DuckPHP 主推的东西，只是为了兼容而附带的扩展」，于是：
- 它**不单独成章**，内容并入**当时**的 2-10 请求生命周期与钩子点（M13 后拆成 [2-2](guide/lifecycle.md)/[2-3](guide/route-hooks.md)）——路由钩子三位置/短路语义/内置钩子清单/`RouteHookManager`/`Ext\HookChain` 讲机制，`Ext\MyMiddlewareManager` 只作为「兼容性扩展」一节交代；
- 中间件的**短路语义按现状写、不改源码**（作者裁定 B）：实测中间件里不调 `$next` 就 return 时，返回值被丢弃、`Route::run()` 还会再跑一次默认回调 ⇒ **控制器照样执行**；这一条作为坑写进 2-10，并给正解「要真拦截 → 用路由钩子并返回真值」；
- 后面各章顺次前移，第三卷成为 `3-1`–`3-7`、第四卷 `4-1`–`4-10`，全书仍是 **41 章** + 4 附录。

**第三次裁定（章号形式：单数字 → 卷-章）**：全库 `第 N 章` 交叉引用、各章 H1、总目录章号列、checklist 条目一次性换成卷-章号；单数字写法不再出现（历史叙述里提到旧编号时会明确标注「最初/当时」）。

**落地用什么姿势**（三次改号共同的教训）：必须**单遍替换 + 回调映射**（Python `re.subn`），顺序 `sed` 会链式误改（`14→8` 之后 `8→17` 又把它改走）；而且**「章号表格」的规则不能全库乱套**——本仓库的 `docs/zh/reference/setting.md`、`i18n.md` 里都有「1/2/3 次序」的表格，第一次改号时被误改成 `1-1/1-2/1-3`，只能回滚重做：**表格行替换只对总目录（`guide/index.md`）与 checklist 生效，H1 只在 `docs/zh/guide/` 内生效**。

**校验方式**（下次改章号照抄）：脚本扫 `docs/zh/guide/*.md` 里所有「章号 + 链接」形式的引用（链接文字里带 `第 X-Y 章`、目标指向某篇），用「文件名 → 章号」表反查 → **0 处不一致**；总目录解析出的每一行章号也与文件名对得上；另外 `python3 docs/scripts/check-doc-links.py docs/zh` 仍 0 死链。

## 10. 本轮（第二卷）交付记录

- 章节（19 篇，全部 ≤400 行，总目录 `docs/zh/guide/index.md` 第二卷表）：
  `layers.md`(226)、`routing.md`(221)、`controllers.md`(204)、`views.md`(187)、`database.md`(242)、`model.md`(259)、`helper.md`(198，第 5 轮重写)、`validator.md`(180)、`session.md`(137)、`lifecycle.md`(293)、`exception.md`(207)、`events.md`(127)、`cache.md`(137)、`i18n.md`(123)、`cli.md`(217)、`testing.md`(151)、`security-performance.md`(181)、`user.md`(198)、`admin.md`(185)。
  其中 2-9 原为「会话与用户/管理员体系」一篇（228 行），第 5 轮按作者裁定拆成三章：会话留在 2-9，用户体系与管理员体系放到卷二末尾（2-18/2-19）。
  其中**新写 6 篇**（controllers / views / events / cache / i18n / security-performance），其余 11 篇为改写；`layers.md` 由 581 行拆成「四层规范」并把控制器/视图内容交给 10/11 两章。
- 分工与执行：先由 5 个子代理并行改写（第 15/16/18/19/20/21 章共 6 篇由子代理完成），**其余 11 篇因子代理连续失败改由主代理亲自写**（`routing`/`controllers`/`views`/`database`/`model`/`helper`/`lifecycle` 及 `cli`/`testing`/`security-performance`/`layers`）。教训：子代理适合「一章一文件、明确验收」的活；并行超过 3 个或单个任务超过 4 章时失败率明显上升，**重试前先确认它对文件没有半途写入**。
- 示例策略（守硬约束 2，不新建示例工程）：全部指向现成资产 —— `demo/public/demo.php`（单文件五层 + 路由 + 函数式视图）、`demo/public/dbtest.php`（模型/分页/CRUD 全链路）、`demo/src/*`、`tests/data_for_tests/ZAllDemo`、`tests/data_for_tests/ZThirdDemo`。
- **本轮实测过的命令**（写进章首「怎么跑」）：
  - `php demo/cli.php help|routes|version|DbTestApp:version` —— 用来确认内置七命令、命令组前缀与相位的关系；
  - 临时脚本（跑完即删，未入库）验证 `regConsoleCommand()` + `command_xxx()` + `@command_desc` 能让命令出现在 help 里，以及 `--k=v` 解析成 `['k'=>'v']`、位置参数在 `['--']`；
  - `tests/Ext/MyMiddlewareManagerTest.php` —— 验证洋葱顺序与「中间件短路无效」那条坑。
- 校验：`docs/zh` 站内链接 **1281 条 0 死链**；全量 `php vendor/bin/phpunit --no-coverage` → `OK (92 tests, 556 assertions)`；17 章 H1 与总目录章号逐一对齐（脚本反查）。
- 顺带发现、**未改源码**（留给作者裁定/后续轮次）：
  1. `demo/src/Controller/Commands.php` 里的 `use DuckPhp\Foundation\CommonCommandTrait;` —— 该 trait 在当前 `src/` 中**不存在**（第 2-15 章已注明"别照抄这一行"）；
  2. `demo/public/dbtest.php` 的 `cli_command_prefix` 与 `bin/duckphp` 的 `cli_command_classes` 在 `src/` 里**没有任何读取点**（死选项）；
  3. `demo/src/Model/CrossModelEx.php` 只有 `foo()` 空壳，名字却暗示"跨库模型"（第 2-6 章已写明「不要从名字推断用法」）；
  4. `demo/src/Business/CommonService.php`、`demo/src/Controller/CommonAction.php` 也都是空壳样板（第 2-1 章已按"占位样板"表述，没有把它们说成现成功能）。

## 11. 本轮（第四卷 + 附录 B/C/D）交付记录

- 章节（10 篇，全部 ≤400 行）：`container-phases.md`(126)、`custom-component.md`(161)、`replace-behavior.md`(181)、`embed.md`(153)、`http-server.md`(164)、`multi-entry.md`(152)、`coverage.md`(154)、`doc-maintenance.md`(158)、`troubleshooting.md`(206)、`design-notes.md`(80)。
- 附录：`appendix-snippets.md`(288)、`appendix-migration.md`(123)、`appendix-faq.md`(117)。**全书 41 章 + 4 附录至此全部落稿**。
- 内容来源与吸收：
  - 第 32/33 章吸收 `architecture.md`（`PhaseContainer` 分桶/查找、组件初始化模板、`EXT_*` 语义）与 `components.md`（组件默认启用 vs 扩展默认关闭）；
  - 第 2-10 章吸收 `architecture.md` 的时序；第 2-1 章吸收其四层部分 ⇒ `architecture.md` / `components.md` 已是"可删"状态（删除动作归 M5）；
  - **纠错**：`architecture.md` 里写的公共桶名 `@public@` 是错的，源码是 `#public`（`KernelTrait`）；相位名也不是类名（是 `:<name>`）——第 4-1 章按源码写，M5 删旧文时不会被带偏。
- 分工与执行：3 个子代理并行（32/33、35/37 各成一个；34/36 的那个跑了 25 分钟无产出，被**主动中断**后由主代理接手写），其余 7 篇（34/36/38/39/40/41 + 附录 B/C/D）由主代理完成。教训补充：子代理长时间零产出时，**先 interrupt 再自己写**，别让它和你抢同一个文件。
- 校验：`docs/zh` 站内链接 **1613 条 0 死链**；10 章 H1 与总目录逐一对齐；全量 `php vendor/bin/phpunit --no-coverage` → `OK (92 tests, 556 assertions)`。
- 本轮"如实标注"的三处（避免把示意当现成功能）：第 4-2 章的 `HelloBanner` 组件、附录 B 里标 ⚠️ 的 HTTPS/CSRF/上传片段、第 4-3 章"谁赢"优先级表（属经验总结而非源码常量）。
- **M5 收尾见下一节。**

## 12. M5 收尾记录（本轮完成：指南全书交付）

- **全局函数参考不新建页**（作者裁定）：由现成的 `docs/zh/reference/Core-Functions.md`（208 行，含 17 个函数的全集签名表与分组讲解）承接。
- **删除的被吸收文件**（都还在 git 历史里，需要时 `git show` / `git checkout` 取回）：
  - `docs/zh/guide/architecture.md`（532 行）—— 内容分别进了第 4-1 章（容器/相位/组件初始化）、第 2-10 章（时序）、第 2-1 章（四层）；
  - `docs/zh/guide/components.md` —— 进了第 4-2 章（组件与扩展）；
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
- 校验：`src/` 未被改动（`git diff -- src` 为空）；`bash docs/scripts/check-non-ascii.sh` → `Total non-ASCII lines: 0`；全量测试 **`OK (93 tests, 565 assertions)`**（比原基线 `92/556` 多 1 个测试方法 + 9 个断言，即这两条回归测试）。

## 14. M6：章号形式 + 类名链接 + 清掉过时内容（作者裁定）

作者三条要求 → 全库执行：

1. **章号改成「卷-章」号**（单数字废弃）：`1-1`–`1-7`、`2-1`–`2-17`、`3-1`–`3-7`、`4-1`–`4-10`，附录仍是 A/B/C/D。落地范围：41 章 H1、总目录章号列（41 行）、41 条 checklist 条目、全库 `第 N 章` 交叉引用（含范围写法 `第 8–24 章` → `第 2-1–2-20 章`、「第X卷 A–B」→「第X卷 A'-B'」），共 **49 个文件、约 700 处**。
   - **踩坑记录（值得记住）**：第一次整库替换时把「表格首列是数字」和「`# 数字`」的规则套到了所有文件上，结果把 `docs/zh/reference/setting.md`、`docs/zh/guide/i18n.md` 里「次序 1/2/3」的**普通编号表格**改成了 `1-1/1-2/1-3` ——**只能 `git checkout` 回滚重做**。正确做法：**表格行替换只对总目录 `guide/index.md` 与 checklist 生效，H1 只在 `docs/zh/guide/` 内生效**；另外总目录里有的表被 Obsidian 加了对齐空格，正则要写成 `^\|\s*(\d+)\s*\|`。
2. **类名第一次出现链接参考手册**：脚本从 `docs/zh/reference/*.md` 的 H1（类全名）建表（113 个类页），在每个指南文件里给**每个类**找**第一次出现**并加链，共 **45 个文件、新增 359 处**（净增 347；现在每章/每附录都至少有一条指向参考手册的类链接）。
   - 规则：`Base`/`Helper` 有多个同名页 ⇒ **只用全限定名形式**链接；短名支持 `Ext\Xxx` / `Core\Xxx` 这类部分命名空间写法；`Xxx.php` 文件名不链接；优先链**正文里的裸出现**，没有裸出现时才退回「链整段行内代码」（例如把 `` `DuckPhp\Foundation\Model\ModelHelperTrait` `` 整段链到 `reference/Foundation-Model-ModelHelperTrait.md`）；
   - **踩坑记录（三个，都踩过）**：
     a. 第一版脚本**没跳过围栏代码块**，把 76 处链接插进了 PHP 示例里（示例没法直接复制）⇒ 必须先把每个文件的行标记成「是否在 ``` 围栏内」；
     b. 第二版在处理**行内代码**时，以为「反引号里就只有类名」，结果把 `` `View::getViewFile()` `` 整段替换成了 `` [`View`](…) `` ——**吞掉了 `::getViewFile()`**。正确做法是：反引号里如果还有别的内容，要么整段当链接文字、要么跳过该处另找；
     c. 同一行/相邻行会被插成同页重复链接（如 `App` 与 `DuckPhp\Core\App` 同时出现）⇒ 收尾要跑一遍「同行或相邻行同页去重」。
3. **不再写旧文档的过时内容**：清掉 5 处「旧文档里的 X 已失效/旧文档遗留/按旧文档找不到」的表述，改成直接给当前事实（`user_callback_for_*`、`docker/test-php84/` 才有脚本、`onBeforeRun/onAfterRun` 框架里不存在、`assignRewrite` 键要带 `/`）；`docs/zh/guide/intro.md` 里那处把「⏳ 撰写中」当章号用的过时说法，也改成了指向 4-9 的正式链接。
   - 三条新规矩已写进 §1 硬约束（第 6/7/8 条）与 §2 模板约定（含「代码块里不插链接」）。

**校验**：41 章 H1 / 总目录 / 交叉引用 / 无单数字章号 → **0 处不一致**（脚本反查，含「章号+链接」配对）；`docs/zh` 站内链接 **1973 条 0 死链**；围栏代码块内链接 **0 处**；行内代码段内容未被截断（抽查 `View::getViewFile()` 完好）；`docs/zh` 全 UTF-8；`docs/zh/guide/` 仍是 `index.md` + 41 章 + 4 附录（46 个文件）。

## 15. M7 / 本轮（阶段五）：Helper 章重写 + 视图级开关机制改写

**背景**：Helper 体系重构（层 Helper 改名 `Foundation\<层>\<层>Helper`、并集改 `__callStatic`、`ModelHelperTrait` 归位 `Foundation\Model`）之后，指南有 11 章带失效表述；另外作者在对话外把「登录后视图」的开关从选项 `use_user_view`/`use_admin_view` 改成**视图数据** `__logined_enable_view`（由 `UserControllerBase`/`AdminControllerBase` 自动 `assignViewData`）。

**做了什么**

- `helper.md` 整章重写（198 行）：四层四个类的对照表、两种工程写法（`extends` / `use … as Helper`）、并集 `__callStatic` 的顺序与代价、`Model\Base` 走 trait 的说明；常见错误表新增 4 行（并集未定义方法、胜出方不符预期、反射看不到、`__logined_enable_view`）。
- `external-auth.md` 的「视图级开关」整节改成 `__logined_enable_view` 机制（含手动开关写法与「继承 `UserControllerBase` 即自动开」）；随后按作者裁定**把这一章拆成三章**：`session.md`（2-9 会话）、`user.md`（2-18 用户体系）、`admin.md`（2-19 管理员体系，含 PermissionMenu），并顺手核对了两处源码已改的事实（`user_provider`/`admin_provider` 选项已不存在、未登录异常类写死为 `UserException`/`AdminException`）；`overriding.md`（表格行 + 示例）、`embed.md`、`static-resources.md`、`appendix-glossary.md`、`layers.md`、`model.md`、`events.md`、`cache.md`、`lifecycle.md`、`security-performance.md`、`project-structure.md` 的旧名/旧选项一并改。
- 顺手修掉 `layers.md` 里那个**不存在**的示例路径 `tests/data_for_tests/ZAllDemo/src/Controller/Helper.php` → `demo/src/Controller/Helper.php`。

**踩坑（重要，写脚本改文档的人都该知道）**：分两批替换时，第二批把「新文案」当成「旧文案」单独传了进去（少传了第三个参数），`String.Replace(old, $null)` **直接删掉了 5 行正文**（`cache.md`/`appendix-glossary.md`/`lifecycle.md`/`security-performance.md`/`embed.md`）。教训：批量改文档**每条都必须传「旧→新」两个字符串**，脚本要打印「命中/未命中」计数；`命中数 = 配对数` 是能立刻发现「误删」的唯一信号，改完还要 grep 复查一次目标串。

- 另：**落实作者在指南里留的全部 `//TODO`**（exception.md 3 条 + deployment/events×2/layers×2/lifecycle/quickstart/views/cli 共 10 条；`design-notes.md` 里那两处是「介绍源码 TODO」的正文，保留）：exception.md 的 3 条——① 异常报告器的装配点已移到入口类（`DuckPhp::initComponentsOfInner()`，源码 `src/DuckPhp.php` 第 132–137 行），`ExceptionManager` 不再读这两个选项；② 条件抛不再推荐 `Ext\ThrowOnTrait`（框架一处不用），推荐 `Helper::ThrowOn()` 家族；③ `Ext\ExceptionWrapper` 标注为**不推荐**（改用 `Helper::XpCall()` 或直接 `try/catch`）。同时把「报告器分发」那节的旧三步（按命名空间 + `defaultException()`）改成现状（短类名拼 `on{类名}()`，兜底 `App::_()->_OnDefaultException()`）。

**验收**：`docs/zh/guide` 里 `HelperTrait` / 旧类名 / `use_*_view` / `insteadof` **归零**（只剩 `Model\ModelHelperTrait` 与「旧选项已失效」的说明）；`check-doc-links.py docs/zh` → `broken: 0`；全量测试 `OK (95 tests, 658 assertions)`、LibCoverage `4876/4876 (100.00%)`；`helper.md` 198 行（≤400）。

## 16. M8 / 本轮：新增第 4-11 章「过时与冷门的扩展类」+ 参考页孤儿清零

**背景**：新增的 `docs/scripts/find-unmentioned-classes.py`（**纯链接反查**：扫 `docs/zh/guide/*.md` 里指向 `../reference/*.md` 的链接，按页名比对）首扫发现参考手册有 **19 个类页指南从没链到**，其中 7 个是 `src/Ext/` 里「框架内部不用、指南也没写」的扩展。作者两条裁定：① 这些 Ext 类在第四卷专门写一章介绍；② 那些接口页链到各自的**实现章**里说明。

**做了什么**

- 新增 `deprecated-exts.md`（4-11，122 行）。判据只有一个、可复现：源码里的 `@todo deprecate`（`grep -rn "@todo deprecate" src/` 命中 6 个类：`StaticReplacer`、`MyFacadesBase`、`MyFacadesAutoLoader`、`ExtendableStaticCallTrait`、`ExceptionWrapper`、`HookChain`）。逐个写清「它做什么 / 为什么过时 / 现在用什么」，并如实写出反例：`MiniRoute`、`Misc`、`ThrowOnTrait` **没有**废弃标记，但同样在推荐路径外（`grep -rn "MiniRoute" src/` 只命中它自己）；`Misc` 的五个能力逐一给替代（`RecordsetH` → `__h()`、`RecordsetUrl` → `__url()`/`Route::Url`、`Import` → Composer、`DI` → 相位容器），只有 `CallAPI()` **没有**替代品，明说「需要就照用」。
- 顺手核实并写进正文的两个事实：`MiniRoute` 无法通过选项替换框架路由（`DuckPhp.php` 174 行直接取 `Route::_()`，且它不是 `Route` 子类）；`Core\ComponentBase` **没有**真的 `implements ComponentInterface`（`src/Core/ComponentBase.php` 12 行是注释掉的）——所以那是「鸭子类型」契约。
- 总目录 `index.md`：加 4-11 行，章数 43 → 44（`index.md` 顶部那句是全库唯一的活章数声明）。
- 接口/冷门页按「链到实现章」补链：`user.md`（`UserLoginActionInterface`、`UserLoginServiceInterface`）、`admin.md`（`AdminLoginActionInterface`、`AdminServiceInterface`、`AdminLoginServiceInterface`）、`database.md`（`PagerInterface`）、`http-server.md`（`HttpServerInterface`，换实现要满足的四个方法）、`custom-component.md`（`ComponentInterface`）、`layers.md`（`Business\Base`，顺带把四层基类补齐）、`installer.md`（`RouteHookWebInstallerView`：纯视图文件、改外观走 `web_installer_view`）、`exception.md`（`ExitException`：`use_exit_exception` 下 `SystemWrapper::exit()` 抛它、`ExceptionManager` 原样放行）。
- 同时**纠正一处误导**：`database.md` 的 SQL 导出表原来把 `ByMysql`/`ByPgsql`/`BySqlite` 并列成一行，实际 `SqlDumperSupporter` 的默认映射只有 mysql 与 sqlite（`src/Ext/SqlDumperSupporter.php` 16-19 行），pgsql 要自己加 `database_driver_SqlDumperSupporter_map`。

**验收**：`find-unmentioned-classes.py` → **109/109 个类页全被链到、孤儿 0**（补链前 19）；`check-doc-links.py docs/zh` → **2099 条链接 0 死链**；`deprecated-exts.md` **122 行**（≤400）；`src/` 未改动（`git diff -- src` 为空，无需跑测试）。新工具已登记进 `coverage.md`（§5 工具表）与两份维护指南。

## 17. M9 / 本轮：`RouteHookApiServer` 选项改名为 `apiserver_*`（含 src）

**背景**：作者裁定把 `RouteHookApiServer` 的选项前缀统一为 `apiserver_`（改名前的那套拼法见提交 `77da0c5f`）。**关键点：这不能只改文档**——参考手册的 `options-index.md` / `options-by-class.md` 是 `gen-options-docs.php` 从 `src/` 的 `$options` 生成的，`--check` 会对拍；只改 md 的话下次重新生成就会把旧拼法写回来。所以按「全仓一起改」执行。

**做了什么**（前缀一律改为 `apiserver_`，共 49 处 / 8 个文件）

- `src/Ext/RouteHookApiServer.php` 12 处：5 个生效键（`base_class` / `namespace` / `class_postfix` / `use_singletonex` / `404_as_exception`）+ 2 条注释键（`config_cache_file` / `on_missing`）+ 6 处读取点；
- `tests/Ext/RouteHookApiServerTest.php` 14 处；`demo/src/System/AppWithAllOptions.php` 5 处；`demo/public/api.php` 3 处；
- 文档：`reference/Ext-RouteHookApiServer.md` 9 处、`guide/multi-entry.md` 6 处；两个生成页重跑 `gen-options-docs.php` 重建（11 行），`--check` → up to date。

**顺手修的 docs/代码不一致（改名时暴露出来的）**：`demo/public/api.php` 里配的那个键**源码从来没读过**——`git log -S`（键名见 `77da0c5f` 的 diff）显示它更早在 `487c9cbe` 就被 `base_class` 取代（那个提交的说明就是「改了路由扩展的一个选项，调了好多文档」），而 demo 自 `1b165a7e` 起一直没跟进 ⇒ demo 里的基类约束**从未生效**。本轮一并改成 `apiserver_base_class`（`~BaseApi`），并在 `multi-entry.md` 常见错误表补了一条「`apiserver_base_class` 写错/漏配 ⇒ 静默 404」。

**踩坑（我自己犯的，记下来）**：前一轮修作者提交红灯时（`bef4d50e`），我在 `src/GlobalAdmin/GlobalAdmin.php`、`src/GlobalUser/GlobalUser.php` 里写了**中文注释**——违反「`src/` 纯 ASCII」这条硬约束，而且当轮没跑 `check-non-ascii.sh`（以为只改了 CSS/逻辑），直到本轮才被闸门抓出 5 行（另 1 行来自 `0f612538 修复多相位`）。教训：**改了 `src/` 就当场跑 `check-non-ascii.sh`，别攒到下一轮**；中文说明写在提交信息里、不写在源码注释里。本轮已把 5 行全改成英文注释。

**验收**：`check-non-ascii.sh` → **Total non-ASCII lines: 0**；`gen-options-docs.php --check` → up to date；`check-doc-links.py docs/zh` → **2103 条链接 0 死链**；全量测试与覆盖率见下；作者裁定**不保留旧拼法的说明**（旧名不再出现在任何文档里，需要时从提交 `77da0c5f` 的 diff 取），`grep -rn` 旧拼法在全仓 md/php 里为 **0**。

## 18. M10 / 本轮：落实 `helper.md` 的两条 `//TODO`（工程 Helper 的动态方法 + 静态覆盖）

**背景**：作者在 `helper.md` §1「工程侧的 `Xxx\Helper` 有两种写法」之后留了两条 `//TODO`：① 说明工程侧 Helper 新增的方法都是「动态方法」，并讲清「你的工程除 `System/` 外不要引用 `DuckPhp` 的东西，应经工程侧 Helper 或 Base 引用」；② 说明工程侧的静态方法用于 override 父类实现。

**做了什么**（`helper.md` 200 → 240 行，仍 ≤400）

- 删掉两行 `//TODO`，在写法 A / 写法 B 的代码块之后补四段正文：
  - **动态扩展点 = 动态方法 + 单例调用**：自己新增的工具方法写成**动态方法（实例方法）**（`public function money()`），调用走**单例** `Helper::_()->money(12.5)`；`_()` 来自 `SingletonExTrait` → `PhaseContainer::GetObject(static::class)`，拿到的是当前相位里你自己那个 Helper 实例（能带实例状态）；
  - **⚠️ 动态方法只能经「你工程那个类」的 `_()` 调**：并集 `Foundation\Helper` 自己没有 `_()`，`DuckPhp\Foundation\Helper::_()` 会被 `__callStatic()` 按派发顺序派到第一站的 `SystemHelper`——**实测返回 `SystemHelper` 实例**（不是你的 Helper）；`DuckPhp\Foundation\Helper::money()` 报 `Call to undefined method`（96 条 `@method` 里没有你的方法）；
  - **除 `System/` 外不要直接 `use` `DuckPhp\*`**：要框架能力经本层工程 Helper（框架方法照旧静态调、自己的工具方法做成动态方法）或本层 Base 的 `_()`；与 `layers.md` §1 的编码规则互链；
  - **静态方法用于覆盖父类实现**（与动态方法分工明确）：给了 `Show()` 先补 `page_title` 再 `parent::Show()` 的示例，并写明两个前提——只对「**经你工程类名**」的调用生效（框架内部是硬编码框架类名调用：`UserControllerBase` / `AdminControllerBase` 的 `ControllerHelper::checkInstall()` / `assignViewData()` / `Show302()`，源码 20-37 行，改那类行为要用 `onLoginedException()` 这类钩子或第 4-3 章手段）；覆盖的**签名必须兼容**。
- 「常见错误」表补两行：并集那条补「你工程 Helper 里新增的动态方法也不在其中」；新增一行「`Helper::money()` 报 `Non-static method ... cannot be called statically`」→ 改成 `Helper::_()->money(...)`。

**四处断言全是实测的**（不是凭记忆写的，脚本 `php check_helper_pattern.php`，跑在仓库根目录）：① `MyH::_()` 返回 `MyH`；② `MyH::_()->money(12.5)` 正常返回；③ `Foundation\Helper::_()` 返回 `DuckPhp\Foundation\System\SystemHelper`；④ `MyH::money(1)` 抛 `Error: Non-static method MyH::money() cannot be called statically`。另外（静态覆盖那条）：静态方法不兼容覆盖是**编译期致命错误**（`php -l` 实测 `Declaration of B::f() must be compatible with A::f($x = 1)`），`parent::` 调静态方法可行（实测输出 `A1B`）。

**修正记录**：本条第一版把 TODO ① 写成了「加**静态**方法、调用点始终 `Helper::`」——作者指出理解有误（是**加动态方法、单例调用**），随后按实测重写，并同步改了写法 A 的示例（`money()` 由 `static` 改为实例方法、示例里补 `Helper::_()->money(12.5)` 调用行）。教训：TODO 里的「动态方法」是 DuckPHP 的固定说法（相对「静态方法」），不要按「运行时可扩展」去自由发挥。

**验收**：`docs/zh/guide` 里再无作者留的 `//TODO`（剩下 4 处命中都是「介绍源码 TODO」的正文，按约定保留）；`check-doc-links.py docs/zh` → **2105 条链接 0 死链**；`helper.md` **227 行**（≤400）。

## 19. 本轮：`doced`→HEAD 代码变动波及指南 —— 只记 TODO，不改指南（作者裁定）

**背景**：本轮任务是「全量覆盖测试 + 按 `doced` 以来的代码变化改参考手册」，作者明确指示：**指南（`docs/zh/guide/`）本轮不动**，凡是被代码改动弄失效的地方，就地留下 `//TODO` 标记，下次改指南时再处理。

**为什么指南会失效**（一句话：`GlobalAdmin`/`GlobalUser` 被重写成「`Admin`/`User` + `globaladmin_*`/`globaluser_*` 选项族」，而指南第 2-18/2-19 章等是按旧 API 写的）：

| 旧（指南里还在写） | 现（源码） |
|---|---|
| `user_callback_for_id/name/data/local_service/session/login_service/add_ext_view_data`、`user_url_*`、`user_enable`、`user_loginout_auto_redirect`、`user_view_file_*` | `globaluser_login_session` / `globaluser_local_service` / `globaluser_login_service` / `globaluser_ext_view_data_callback` / `globaluser_need_login_callback` / `globaluser_url_{home,register,login,logout}` / `globaluser_is_authed_redirect` / `globaluser_view_file_{header,footer}` / `globaluser_enable_callback_singleton`（admin 侧同构，前缀 `globaladmin_`） |
| `GlobalUser::_()` / `GlobalAdmin::_()` | `User::_()` / `Admin::_()`（`GlobalUser`/`GlobalAdmin` 只是实现，注册在父类名这个键上） |
| `UserException` / `AdminException` 两个类 | 类已删除；改用 `User::EXCEPTION_*` / `Admin::EXCEPTION_*` 常量；未登录由 `throwLoginOn()` 处理（自定义回调 / 302 / Ajax JSON + `exit()`） |
| `onLoginedException(AdminException $ex)` / `onLoginedException(UserException $ex)` | `onNeedPermission()`（**无参**；Ajax 输出 `error_code: -1`(admin) / `-2`(user)、`error_message: 'NEED_PERMISSION'`） |
| `__logined_enable_view` / `__logined_enable_header_footer` | `__use_logined_view_data` / `__use_logined_header_footer_file`；另有 `__logined_render_header_footer`（缺省真）控制是否渲染头尾文件 |
| `GlobalAdmin::_Show()` / `GlobalUser::_Show()`、`go_url()`、`addExtViewData()`、`getLoginBusiness()` | 全部删除；接管渲染的是 `DuckPhp::_Show()` → `Admin::_()`/`User::_()` 的 `mergeViewData()` |
| 层 Helper 上的 `public static $EVENT_REGISTERING` 等属性 | 改成常量：定义在 `User`/`Admin` 上，层 Helper 里只留**同名别名常量**（值形如 `'ACTION_USER_LOGINED'`，不再是 `'registering'`） |

**本轮做了什么**（只动 11 个 guide 文件：其中 **7 个加了 `//TODO` 标记**、另 4 个只是零散旧名/行号就地改正；**没有**重写任何一章）

- 顶部整块 TODO（篇幅最大、要整章重写）：`user.md`（49 处旧 API）、`admin.md`（30 处）；
- 就地 TODO / 顺手改掉旧名（带 `//TODO`）：`events.md`（事件常量表整张过期）、`overriding.md`（视图级开关表 + 第 105-106 行示例，两处标记）、`appendix-snippets.md`（登录片段）、`exception.md`（两个已删异常类）、`embed.md`；
- 零散旧名**就地改正**（不留标记）：`static-resources.md`、`troubleshooting.md`、`appendix-faq.md`、`helper.md`（`onLoginedException()` → `onNeedPermission()` 与源码行号）；`user.md` / `admin.md` 里的死链与「未登录抛异常」两处也在标记之外顺手改了；
- 6 处指向**已删除参考页**的链接（`GlobalUser-UserException.md` / `GlobalAdmin-AdminException.md`）已就地改成文字，保证 `check-doc-links.py` 仍为 0 死链（`user.md` 2 处、`admin.md` 2 处、`exception.md` 2 处）；
- 参考页里的同名死链由本轮参考手册同步一并清掉（`Core-DuckPhpSystemException.md`）。

**验收**：`check-doc-links.py docs/zh` → **2104 条链接 0 死链**；`docs/zh/guide/` 里新增 `//TODO` 标记 **7 个文件**（下次改指南的入口：`grep -rn '//TODO（参考手册同步轮' docs/zh/guide`）。

**下次改指南的建议顺序**：先 `user.md` / `admin.md`（旧 API 最集中，且两章互相引用），再 `events.md`（事件常量表），然后零散旧键名（`appendix-snippets.md` / `troubleshooting.md` / `appendix-faq.md` / `static-resources.md` / `embed.md` / `overriding.md` / `helper.md` / `exception.md`）；改完按 §4 跑链接检查与行数检查，并把对应 `//TODO` 删掉。

## 20. M11 / 本轮：照 §19 的清单把指南同步完（`//TODO` 清零）

**背景**：作者看完 §19 的清单后说「现在开始改 guide 文件」——本轮就是按那个顺序把 13 个文件改完，`//TODO` 清零。

**做了什么**

- **`user.md`（2-18）与 `admin.md`（2-19）重写**（205/193 → **231/178 行**）：选项对照表整张换成 `globaluser_*` / `globaladmin_*`；调用侧改成 `User::_()` / `Admin::_()`；「未登录怎么办」改成 `throwLoginOn()` 三分支表（自定义回调 / 302 带 `?b=` / Ajax JSON，最后都 `exit()`）；新增「登录后视图：三份视图数据」小节（`__use_logined_view_data` / `__use_logined_header_footer_file` / `__logined_render_header_footer`，并写明判定与渲染都在 `DuckPhp::_Show()` 里）；admin 侧补 `onNeedPermission()` 钩子与 `isSuper()`；两章的示例都换成**实测过**的写法（应用级选项 + `ext` 三种写法，见下）。
- **`events.md`（2-12）**：事件常量表从「层 Helper 上的 `public static $EVENT_*` 属性」改成「`User`/`Admin` 上的常量 + 两张分工表」——`EVENT_ACTION_*` 由框架派发（`GlobalUser`/`GlobalAdmin` 的登录注册登出），`EVENT_SERVICE_*` **框架不派发**、是留给服务层自己 `fire()` 的名字；补了两段监听/派发示例。
- **`overriding.md`（3-5）**：视图级开关表与示例改成新键名，并写清「命中接口 → `Admin::_()`/`User::_()` 的 `mergeViewData()` → `View::setViewHeadFoot()`」这条链路；表里「本卷示例位置」由第 2-9 章改指第 2-18 章。
- **`appendix-snippets.md`**：登录片段换成三个必需回调 + 「想自己接管跳转就传 `check_login=false`」；
- **`exception.md`（2-11）**：删掉「框架自带 `UserException`/`AdminException` 继承 `\Exception`」那句，改成当前事实（登录/权限不用异常类，错误码在常量上，未登录/无权限由 `throwLoginOn()` / `onNeedPermission()` 处理）；
- **`doc-maintenance.md`**：把「批量改名时的例外」那条的例子（旧键名链）改成「账本类文件才保留沿革」的通用说法；
- 零散旧名/死链：`static-resources.md` / `troubleshooting.md` / `appendix-faq.md` / `helper.md` / `embed.md`（`onLoginedException()` → `onNeedPermission()`、源码行号、`__use_logined_view_data`、`globaluser_login_session`）。

**示例全部实测**（不是照抄旧文）：本轮写了个临时脚本（跑完即删，仓库根 `tmp_verify_user.php`）在 WSL 里跑通 **19 条断言 / 0 失败**，覆盖两章正文里的每个断言性说法：
① 应用级 `globaluser_*` 选项在 `'ext' => [MyUser::class => true]` 下确实落到组件；② `User::_()` 是工程子类的 `PhaseProxy`；③ `id()/name()` 读会话、`canAccess()` 走 Service、`service()->batchGetUsernames()` 经代理；④ `login()` 调登录服务 + 写会话 + 302 到 `globaluser_url_home`；⑤ 未登录 `id(true)` 302 到 `globaluser_url_login`、`id(false)` 返回 0；⑥ `__use_logined_view_data` + `__use_logined_header_footer_file` 把头尾套上（输出 3 个 `Block`）且视图数据里有 `__logined_id`；⑦ Admin 侧同样吃应用级 `globaladmin_*` 选项、`isSuper()` 走 Service；⑧ `Helper::OnGlobalEvent('ACTION_USER_LOGINED')` 能被 `login()` 触发，而 `EVENT_SERVICE_*` 监听不到（框架不派发）。

**顺手学到的两条事实**（已写进两章）：
- `globaluser_view_file_header/footer` 这类值是**按 `getOverrideableFile('view', …)` 解析**的，相对路径落在 **`<应用 path>/view/`** 下，**不是** `path_view` —— 一开始就是踩了这个才渲染失败（报 `include(.../view/block.php): Failed to open stream`）；
- `App::_()` 之所以能拿到「当前应用」，是因为 `KernelTrait` 在初始化时 `(self::class)::_($this)` 把自己注册进容器；**换过相位容器后（`PhaseContainer::RestAllContainerForTesting()`）必须重新 init**，否则 `User::_()` / `App::_()` 会新建一个「默认的」实例，症状是莫名的 Internal Error。

**验收**：`check-doc-links.py docs/zh` → **2109 条 0 死链**；`docs/zh/guide` 里同步轮的 `//TODO` **归零**；改动的 12 个文件 117–294 行（≤400）；「链接文字章号 vs 目标页 H1」**0 处不一致**；`find-unmentioned-classes.py` → **109/109 类页全被链到**；全量测试 `OK (96 tests, 823 assertions)`（本轮**未改 `src/` 与 `tests/`**，两条基线沿用上一轮的实测值）。

## 21. M12 / 本轮：2-18/2-19 改成「使用」章，实现另立 4-12/4-13（作者裁定）

**背景**：M11 把两章同步到新 API 后，作者反馈「**user.md 这章写得不好，应该是怎么用先**，也就是 `(Controller)Helper::User/UserId/UserName/UserService` 和 `(Business)Helper::UserService` 的使用；后面才讲实现，**甚至可能实现要放到高级内容**」。追问后的裁定是：**2-18 = 使用用户系统、2-19 = 使用管理员系统；高级卷分为 4-12 实现用户系统、4-13 实现管理员系统**。

**怎么分的家**

| | 卷二（怎么用） | 卷四（怎么实现） |
|---|---|---|
| 读者 | 在控制器/业务里调 API 的人 | 项目搭建者、要换会话/服务实现的人 |
| 内容 | `Helper::User()/UserId()/UserName()/UserService()`（控制器）与 `Helper::UserService()`（业务）的入口表、`Helper::User()` 方法一览、未登录的三条路与 `check_login=false`、登录/注册/登出三个动作、两个视图开关、常见写法与错误 | 三件实现（会话/登录服务/本地服务）的骨架、`ext` 三种写法、`User` 是键 `GlobalUser` 是实现、全部选项表、`UserSessionTrait`、服务的每个方法被谁调用、`need_login_callback`、`ext_view_data_callback`、头尾文件解析规则、自检四条 |
| 行数 | `user.md` 231、`admin.md` 178（M11 时） → **本轮重写后 214 / 198** | 新写 `impl-user.md` 214、`impl-admin.md` 173 |

**两章使用章的结构**（照 §2 模板，但把「机制说明」写成了调用方视角）：

1. 最小示例：控制器里 `Helper::UserId()/UserName()`，业务里 `Helper::UserService()->batchGetUsernames()`；
2. 入口表：**控制器拿「人」、业务拿「服务」**——并写清为什么业务层没有 `User()/UserId()`（「现在是谁」是请求上下文，业务方法要用户信息就显式传参，这样 CLI/队列也能复用）；
3. 未登录三选一表 + `check_login=false`；
4. `Helper::UserService()`（控制器与业务都能用，`$user_id` 显式传参）；
5. 登录/注册/登出三个动作（自动 302 由 `globaluser_is_authed_redirect` 控制）；
6. 两个视图开关 + 继承 `UserControllerBase` 时已自动置真。

**顺带查清的两件事**

- **两个入口的实现其实一样**：`ControllerHelper::UserService()` 与 `BusinessHelper::UserService()` 都是 `User::_()->service()`（`src/Foundation/Controller/ControllerHelper.php` 265-268、`src/Foundation/Business/BusinessHelper.php` 101-104），但**只有控制器层**有 `User()/UserId()/UserName()`。业务层要「谁」只能由控制器传参——这条以前没人写清，现在写在 2-18 第 1 节。
- **组件不判断「登录成功没有」**：`GlobalUser::login()` 把登录服务的返回值**原样** `setCurrentUser()` 写进会话，再发完成事件、再 302；所以「密码不对」的表现由登录服务决定（返回空数组最省事）。这条写在 4-12 第 5 节。

**示例实测**：临时脚本（跑完即删 `tmp_verify_usage.php`）**13 条断言 / 0 失败**，逐条对着使用章的正文：`Helper::User() === User::_()`、`UserId()/UserName()/data()` 读会话、`Helper::User()->canAccess()`（无参＝当前路由）与 `log()` 走 Service、`Helper::UserService()` 与 `BusinessHelper::UserService()` 都能批量取名字、未登录时 `UserId(false)` 返回 0 而 `UserId()` 302 到登录页、`login()` 写会话并 302 到 `globaluser_url_home`、控制器置真两个视图键后头/正文/尾都渲染。

**连带改动**（新增两章要动的账）：`guide/index.md` 的第四卷表加 4-12/4-13 行、章数 44 → **46**、「第 1-1–4-11」→「第 1-1–4-13」、2-18/2-19 两行标题改成「使用…」；`guide-maintenance-guide.md` §1 卷表章数、硬约束 6 的章号范围、新增硬约束 10（用/实现分家）、§9 章序表 2-18/2-19 行；`guide-rewrite-checklist.md` 第四卷表 + M12 行；全库 5 处「第 2-18 章 用户体系 / 第 2-19 章 管理员体系」的链接文字（`controllers`/`helper`/`session`/`static-resources`/`validator`）批量改名；`session.md`、`appendix-snippets.md`、`appendix-migration.md`、`troubleshooting.md`、`layers.md` 的指向改成「用/实现」两处；`controllers.md` 里那段 `UserAction::_()->login($name, $password)` + `Session::_()->setUserId()`（两个都不存在的 API）换成 `NoteAction` 的真实写法。

**校验**：见下一轮收尾（链接/章号/行数/orphan 四项一起跑）。

## 22. M13 / 本轮：拆开「请求生命周期与钩子点」，两章提到 2-2/2-3（作者裁定）

**背景**：作者一句「拆分 请求生命周期与钩子点；请求生命周期要讲到有哪些内置组件；钩子点改名路由钩子」，以及追问编号时的回答「**放在第 2，第 3 章位置**」——即这两章不再是卷二末尾的 2-10，而是紧跟 2-1 四层规范之后的 2-2/2-3，卷二其余各章顺次后移。

**结果**：卷二从「2-1–2-19」变成「**2-1–2-20**」，全书 46 → **47 章**；两章的落点：

| 新号 | 章 | 文件 | 拿到了什么 |
|---|---|---|---|
| 2-2 | 请求生命周期 | `lifecycle.md`（192 行） | `init()` 八步 + **框架默认装了哪些内置组件**（新写的第 2 节）+ `serve()` 时序 + `onBeforeOutput()` + CLI 分流 |
| 2-3 | 路由钩子 | `route-hooks.md`（新文件，213 行） | 六个位置与短路语义 + `RouteHookManager` 增删改查 + 内置钩子位置表 + 选型表 + 洋葱中间件 + `HookChain` |

**2-2 新写的「内置组件清单」**（作者点名要的内容，来源：`KernelTrait::initComponents()` + `App::initComponentsOfRoot/OfInner` + `DuckPhp::initComponentsOfRoot` + `$common_options['ext']`）：

- **root 层**（只根应用装配，且登记为「公共类」⇒ 各相位共用同一实例）：`Console`（跟随应用选项）、`SystemWrapper`/`Logger`/`CoreHelper`（`EXT_SKIP_INIT` 只建实例不 init）、`DbManager`/`RedisManager`/`Admin`/`User`/`GlobalEvent`（`EXT_ROOT_HOLD_POSISION_ONLY` 只占位；前两个还要有 `database`/`redis` 配置才 init）、`ExtOptionsLoader`（仅 `data_file_enable` 为真）；
- **inner 层**（每个相位一套）：`Route`、`View`、`Configer`；
- **ext 层**（应用 `ext` 里声明；框架默认打开）：`Lang` + `RouteHookRewrite`/`RouteHookRouteMap`/`RouteHookResource`/`RouteHookPathInfoCompat`；
- 另外两样不是组件但会就位：`ExceptionManager`（`initException()`，早于组件）、`Runtime`（按需创建，`use_output_buffer` 用它）；
- 读表要点写成三条规矩：root vs inner = 「跨相位共享与否」、「只占位」= 登记类名但不在 init 阶段创建、「只建实例」= 纯工具组件。

**重排怎么做的（下次改号照抄）**：脚本**单遍正则 + 回调映射**（`第 2-(\d+) 章` / `^# 2-(\d+) ` / `^\| 2-(\d+) \|` 三条，映射表 `{2:4,3:5,…,9:11,11:12,…,19:20}`），**排除**已经按新号写好的两个文件，避免二次偏移；老 2-10 的 44 处引用逐条判读后分派——**钩子语义/中间件/短路**那批（`appendix-faq`/`appendix-migration`/`appendix-snippets`/`custom-component`/`deprecated-exts`/`design-notes`/`events`/`routing`/`security-performance` 部分行/`troubleshooting` 部分行/`index`）→ 2-3 且链接目标改成 `route-hooks.md`，**时序/初始化/输出**那批（`advanced-phase`/`cli`/`controllers`/`exception`/`http-server`/`multi-entry`/`quickstart`/`session`/`security-performance` 部分行/`troubleshooting` 部分行）→ 2-2。第一遍有 15 处没匹配上（链接写成 `../guide/lifecycle.md`、标题写成「生命周期」而非「请求生命周期与钩子点」、或者干脆是裸号/代码块里的裸号），第二遍按「精确子串」逐条补掉。
**踩坑**：TOC（`guide/index.md`）的行号不会因为重排而移动——只改数字会得到「号码对但顺序乱」的表，必须**整块重写并按新序排**；同理 `layers.md` 的「本卷地图」表里是更早的单数字（8/9–13/14–16/17–19/20–24），要手工重画。

**顺带修**：`layers.md` 的「本卷地图」表重画成新分组（规范 / 框架机制（先看懂时序） / 请求路径 / 横切能力 / 框架机制 / 进阶 / 用户与管理员体系）；`doc-maintenance.md` 的「四卷 44 章」→ 47；`intro.md` 两处「2-1–2-17」→「2-1–2-20」；`guide-maintenance-guide.md` §1 卷表章数、硬约束 6 的章号范围、§6 的章数说明、§9 的章号表（含「读历史记录注意」提示）、§9 里那个旧编号例子（单数字 11 → 现在是 2-6，不是 2-4）。

**验收**：`check-doc-links.py docs/zh` → **2237 条 0 死链**；章号一致性脚本 → **0 处不符**、`index.md` 之外无缺章号 H1；`grep -h '^# 2-' docs/zh/guide/*.md` 恰好是 `2-1`…`2-20` 二十行；新写的 `route-hooks.md` 213 行、改写的 `lifecycle.md` 192 行（全书最大章 294 行，均 ≤400）；`src/`、`tests/` 未改动。