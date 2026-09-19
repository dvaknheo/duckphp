# 用户指南重写 · 交接与维护指南

> 目标读者：接手「`docs/zh/guide/` 用户指南重写」这件事的 AI/工程师。
> 一句话任务：**把用户指南从「平铺清单」重写成「一页总目录 + 四卷 42 章 + 4 附录」**。
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
| 二 单一应用 | 会跑 | 独立写完一个业务应用（覆盖主流框架的全部常见主题） | 18 |
| 三 使用第三方应用 | 会写单应用 | 把外部应用挂进来、共享组件、覆盖它的内容 | 7 |
| 四 高级话题 | 会集成 | 改框架行为、调优、排错、维护文档 | 10 |

**硬约束**（作者已裁定，别自行放宽）：

1. **一页总目录** = `docs/zh/guide/index.md`；`docs/zh/index.md` 只做指路，不复制章表。
2. **示例只用现有代码**：`demo/`、`tests/data_for_tests/ZAllDemo`、`tests/data_for_tests/ZThirdDemo`；不新建示例工程（`ZThirdDemo` 是唯一获准新增的）。
3. **新写的章 ≤400 行**；改写章暂不限，但明显超长的要拆（见 Checklist 的 Q2）。
4. **附录**：`appendix-global-functions.md` 与 `appendix-options.md` **迁入参考手册**（由作者在新对话建那两页），指南侧只留指向参考手册的说明。
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

参考样板：[第 26 章](guide/advanced-phase.md)（机制讲清 + 坑位标注）与 [第 30 章](guide/overriding.md)（含「谁赢」的排查思路）。

## 3. 示例与验证资产

| 资产 | 服务哪几卷 | 怎么跑 |
|---|---|---|
| `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php` | 第三卷 26–32 | `wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` |
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

# 2) 按 Checklist 的 M2 顺序推进（建议）：
#    a. 先拆薄两份长文：layers.md → 拆出 10 controllers.md / 11 views.md，自己只留四层规范
#                        lifecycle.md → 拆出 17 的会话节、20 events.md，自己只留生命周期
#    b. 再逐个改写：9 routing → 12 database → 13 model → 15 helper（含修示例）→ 16 validator
#                  → 17 external-auth（含修旧键名）→ 19 exception → 23 cli → 24 testing
#    c. 最后补新章：18 middleware / 20 events / 21 cache / 22 i18n / 25 security-performance（各 ≤400 行）
# 3) 每写完一章：跑 §4 的 ①②③；改过示例就补跑 ④
# 4) 一卷结束：勾 Checklist、登记校验结果、在对话里报一次
```

## 7. 本轮（第三卷）交付记录

- 示例工程：`tests/data_for_tests/ZThirdDemo`（主应用 `src/` + 被挂的 `third/` 第三方应用；四个覆盖点：视图 `view/shop/index.php`、配置 `config/shop/greet.php`、资源 `res/shop/third.css`、控制器 `src/Override/ShopControllerOverride.php`；另有事件总线与两种跨相位调用）。
- 测试：`tests/ZThirdDemoTest.php`，**36 断言**；一次 `init` 多次 `serve()`，逐条断言「有覆盖」与「没覆盖时回落子应用自己那份」。
- 指南：一页总目录 `docs/zh/guide/index.md`、附录 A `appendix-glossary.md`、改写 26、新写 27–32（行数 174/125/120/134/140/122/206，均 ≤400）。
- 其他：`docs/zh/index.md` 瘦身成指路页；顺手修掉 `docs/zh/guide/routing.md`(3 处) 与 `external-auth.md`(4 处) 的相对路径坏链；新增 `scripts/check-doc-links.py`。

## 8. 本轮（第一卷）交付记录

- 章节（总目录 `docs/zh/guide/index.md` 第 1–7 行）：`intro.md`(108)、`install.md`(161)、`project-structure.md`(136)、`quickstart.md`(226)、`configuration.md`(141)、`debugging.md`(122)、`deployment.md`(164) —— 新写 2 篇（intro / project-structure），改写 5 篇；行数全部 ≤400。
- 示例：不新造工程，全部挂在现成的 `demo/` 上（`quickstart.md` 的便签列表走五层；`deployment.md` 的内置服务器写法实测过 `php -S 127.0.0.1:8080 -t demo/public demo/public/router.php` 一类的 router 要点）。顺手修掉 `demo/public/helloworld.php` 里 `catch (\Thowable $e)` 的旧文档错字（`catch` 不触发自动加载 ⇒ 该 catch 永不命中）。
- 校验：`docs/zh` 站内链接 **1005 条 0 死链**；`docs/zh` 153 篇 md 逐字节 UTF-8；全量 `php vendor/bin/phpunit --no-coverage` → **`OK (92 tests, 556 assertions)`**（其中 `ZAllDemoTest` 的 `files` 期望长度 10429 → **10360**，是并行会话的选项表重构所致，已在交接指南第 7 节记录判断依据）。
- 第四卷/附录与第二卷仍未开工；未决问题（Q1–Q4）见 `guide-rewrite-checklist.md` 末尾。
