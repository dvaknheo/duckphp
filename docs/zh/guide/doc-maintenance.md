# 4-8 文档与参考手册维护

> 解决什么问题：改了源码之后，怎么让 `docs/zh/reference/`（110 篇逐类文档 + 4 个汇总页）和 `docs/zh/guide/`（本指南）不变成谎话；以及本仓库把这件事做成了哪几道自动闸门。
> 前置：[第 4-7 章 测试基建与覆盖率流水线](coverage.md)。预计 20 分钟。
> 两份权威手册（本章是它们的「导读」，细节以它们为准）：
> [参考手册维护指南](../reference-maintenance-guide.md)、[用户指南维护指南](../guide-maintenance-guide.md)。

## 最小示例

改完一个类的源码后，最小的一套自检：

```bash
# 以下命令都在仓库根目录下执行
wsl -e bash -lc "python3 docs/scripts/check-doc-links.py docs/zh"   # 期望 broken: 0
wsl -e bash -lc "bash docs/scripts/check-non-ascii.sh"              # src/ 改过就跑，期望 0
# 漂移扫描：把 drift.py 存到临时目录后运行（脚本见维护指南 §5）
python3 <tmp>/drift.py --all
```

再看一眼 `docs/zh/reference/<对应篇>.md`：**方法表、选项表是否与源码一致**。

## 机制说明

### 1. 文档的三层结构（别写串层）

```
docs/zh/index.md            ← 指路页：只告诉你"去哪找"，不复制目录
docs/zh/guide/              ← 用户指南：一页总目录 + 四卷 47 章 + 4 附录（"怎么做"）
docs/zh/reference/          ← 参考手册：一类一页 + 汇总页（"有什么"）
docs/zh/*maintenance-guide* ← 两份维护指南（"怎么维护"）
skeleton/AGENTS.md          ← 工程内约定（随包、随生成工程走）：目录树 / 命名 / 分层规则
```

| 内容 | 该写在哪 |
|---|---|
| 「这个方法签名是什么/选项默认值是多少」 | `reference/`（逐类页 + `options*.md` 汇总） |
| 「我要做 X，该怎么写」 | `guide/`（章节；链接到参考页而不是复制签名表） |
| 「这类改动要注意什么坑」「现在是什么状态」 | 两份 `*-maintenance-guide.md` |
| 「这个工程里有哪些文件、该建什么、不许做什么」 | `skeleton/AGENTS.md`（**唯一一份**文件级清单与工程约定；随包发给用户，所以只讲工程、不复制框架 API） |
| 「这次改了什么、为什么」 | commit message（**别写进文档**） |

**关键的「单一事实来源」原则**：选项表默认值、方法签名只在参考页维护；指南里只用它、不重述。否则源码一改就要改两处，必然漂移。

### 2. 一致性闸门：漂移扫描

> **工具脚本统一放在 `docs/scripts/` 下、跟文档一起提交**：它们只服务文档生成与校验（不属于框架运行时），放在 `docs/` 里便于「文档改动 + 生成器改动」同一次提交。本章与两份维护指南里的命令都从**仓库根目录**执行。

`reference/` 的逐类页与源码之间靠**集合比对**兜底（维护指南 §5 给了完整脚本，存成 `%TEMP%\drift.py` 运行）：

- `missing-method`：源码有、文档没有 → **必须补**；
- `missing-option`：`$options` 里的键文档没写 → **必须补**；
- `extra-option`：文档里写了源码没有的键（死选项/改名残留）→ **必须删或改**；
- `extra-method`：文档「使用方式」示例里自定义的方法 → 一般无需处理。

本仓库当前状态是 **0 不一致**（`missing-*` 全空）。

> ⚠️ `docs/scripts/gen-reference.php verify` 对「`use Trait { … as … }` 并 override」的大文件会**漏列方法**，从而误报「多了方法」。判一致性以漂移扫描为准。

### 3. 新增/改名/改选项时的同步清单

**新增一个类**：

1. 按模板写 `docs/zh/reference/<Ns>-<类名>.md`（H1 = 全限定类名；简介 / 类信息 / 选项表 / 使用方式 / 注意事项 / 方法列表）；
2. 在 `docs/zh/reference/index.md` 登记一行；
3. 如果它属于某主题，在对应指南章里加一句「怎么用」并链接过去；
4. 若新增了选项，跑 `docs/scripts/gen-options-docs.php` 或手改 `options.md` / `options-by-class.md` / `options-index.md`。

**改公共名字（方法名 / 选项键 / 参数名）**：

```bash
grep -rn "<旧名>" src tests docs | wc -l      # 先列全，改完再 grep = 0
```

- 覆盖 `src/` + `tests/` + `docs/zh/reference/` + `docs/zh/guide/`；
- **例外**：`docs/old/`、`docs/en/`、`README*.md` 是归档/陈旧副本，默认不随中文文档同步；批量替换时先排除它们，也别去「顺手对齐」。
- `src/` 里不许出现中文/全角字符——最常见的来源是从中文文档复制粘贴（全角箭头 `→`、全角括号）。改完跑 `docs/scripts/check-non-ascii.sh`，期望 `Total non-ASCII lines: 0`。

**改章号/章序**（本指南重排第二卷时踩过）：交叉引用必须**单遍替换 + 回调映射**（Python `re.subn`），用 `sed` 顺序替换会链式误改（`14→8` 之后 `8→17` 又把它改走）。改完用「文件名 → 章号」表反查所有「第 N 章 + 链接」是否一致。

### 4. 站内链接：0 死链是硬指标

```bash
python3 docs/scripts/check-doc-links.py docs/zh     # 期望 checked N, broken: 0
python3 docs/scripts/check-doc-links.py docs        # 全仓（docs/old、docs/en 有历史死链，属已知）
```

约定：**没写的章不挂链接**——在总目录里用 `⏳ + 纯文本`，写完再换成链接。这样链接校验永远为零死链。

### 5. 不要提交的东西

| 路径 | 说明 |
|---|---|
| `docs/zh/guide/.obsidian/`、`docs/zh/reference/.obsidian/` | Obsidian 本地配置（误入就 `git rm -r --cached` + `amend`） |
| `tests/data_for_tests/ZAllDemoTest-<长度>.txt`、`*/log_*.log`、`test_reports/`、`test_coveragedumps/` | 测试产物 |
| `demo/runtime/*.sqlite`、`runtime/` 下的运行期文件 | 运行产物 |

提交前 `git status --short` 复核一遍（别 `git add .`）。

### 6. 手册本身也要维护

- `docs/zh/reference-maintenance-guide.md`：参考手册侧的「怎么做」+ 工具 + 陷阱表 + 当前状态；
- `docs/zh/guide-maintenance-guide.md`：用户指南侧的模板、约束、校验命令、陷阱表、章序与改号姿势。

纪律：**手册里只留「现在怎么做」与「当前状态」**——「现状」段一两句结论、待办列真没做完的；**改动的过程、逐文件清单、调试经过属于 commit message**（`git log` 就是历史），别往手册里堆流水账。有长期价值的教训写进陷阱表，规则写进硬约束。

## 常见写法

**① 只改了一个方法的可见性**

参考页里改方法条目的可见性词 + 若影响用法则在指南章里补一句注意事项；跑漂移扫描确认无 `missing-method`。

**② 新增一个选项**

参考页的「选项」表加一行（键 / 默认值 / 说明），再跑 `docs/scripts/gen-options-docs.php` 更新汇总页；如果这个选项在指南某章会被用到，在该章补一句。

**③ 源码里发现「读了没声明」的选项**

跑 `docs/scripts/scan-options.py` 看告警。两种处置：要么在 `$options` 里正式声明它，要么把它列进 `$hidden_options` 并把读取点写成 `?? 默认值`（并想清楚「用户还能不能设置它」——见 [`DuckPhp`](../reference/DuckPhp.md)/[`App`](../reference/Core-App.md) 里 `$hidden_options` 的注释）。

**④ 文档里发现链接指向不存在的页**

先用 `check-doc-links.py` 定位，再把链接改成**纯文本 + ⏳**（若目标未写），或补写该页。

**⑤ 提交前的文档自检三连**

```bash
python3 docs/scripts/check-doc-links.py docs/zh      # 链接：期望 broken: 0
bash docs/scripts/check-non-ascii.sh                 # 改过 src/ 时：期望 Total non-ASCII lines: 0
python3 - <<'PY'                                # 文档 UTF-8 检查
import pathlib
bad = []
for p in pathlib.Path('docs/zh').rglob('*.md'):
    if '.obsidian' in p.parts:
        continue
    try:
        p.read_bytes().decode('utf-8')
    except UnicodeDecodeError:
        bad.append(str(p))
print('non-utf8:', bad if bad else 'none')
PY
```

（三条核心指标：**站内链接 0 死链、文档全 UTF-8、`src/` 纯 ASCII**；更完整的现成脚本见维护指南。）

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 参考页与源码不一致，但没人发现 | 只改了源码没改文档 | 跑漂移扫描（§2），把 `missing-*` 清零 |
| 文档里留着**死选项** | 选项被改名/删除，文档没跟 | 漂移扫描的 `extra-option` 就是它；删掉或换成新键名 |
| `sed` 批量改名改坏了别的句子 | 顺序替换（`14→8` 之后 `8→17` 又把它改走） | 单遍替换 + 回调映射（`re.subn`）；改完 grep 复核 |
| 链接校验报坏链 | 指向了还没写的章/页 | 未写的用「纯文本 + ⏳」；写完再换链接（§4） |
| `src/` 里出现中文/全角字符 | 从中文文档粘贴 | `docs/scripts/check-non-ascii.sh` 兜底；改回 ASCII |
| 提交里混进 `.obsidian/` 或测试产物 | `git add .` | 提交前 `git status --short` 复核（§5） |
| 维护指南越写越长、接手的人读不动 | 把「这次怎么改的」写成了正文 | 只留规则 + 陷阱 + 当前状态；过程写进 commit message |
| `gen-reference.php verify` 报「多了方法」就照删 | 该脚本对大文件漏列方法，且只认反引号开头的方法条目（本仓用 4 空格缩进） | 以漂移扫描为准，`verify` 只当参考 |

## 下一步

- [第 4-9 章 性能调优与排错手册](troubleshooting.md)：症状 → 排查路径。
- [第 4-10 章 设计取舍与已知坑](design-notes.md)：哪些行为是刻意的、哪些是坑。
- 维护指南：[参考手册维护指南](../reference-maintenance-guide.md)、[用户指南维护指南](../guide-maintenance-guide.md)。
