# DuckPHP docs — English translation guide

This file is the single source of truth for how `docs/en/` is produced from `docs/zh/`.
Every translator (human or agent) MUST read it before touching a file.

## 1. Ground rules

- **The Chinese tree is authoritative.** `docs/zh/**` is written first; `docs/en/**` mirrors it.
  When a Chinese file changes, the English twin is updated **in the same change** (see the
  maintenance guides).
- **File names and paths are identical** in both trees:
  `docs/zh/guide/route-hooks.md` ↔ `docs/en/guide/route-hooks.md`.
  Relative links therefore stay byte-identical — never rewrite a link path, only the link text.
- **Nothing is invented, nothing is dropped.** Same headings, same order, same tables (same number
  of columns and rows), same code blocks (same number, same statements), same callouts.
- **Code blocks are translated in comments and example strings only**; identifiers, class names,
  method names, option keys, file paths, CLI commands and PHP syntax are untouched.
- **No CJK characters may remain** in `docs/en/**`, except inside code fences where the Chinese is
  deliberately the subject matter (e.g. the i18n chapter's `'你好' => ...` sample). If you keep such
  a string, make sure the surrounding prose explains it.
- **Do not touch anything else**: no edits to `docs/zh/**`, `src/**`, `tests/**`, `demo/**`,
  `skeleton/**`, `docs/scripts/**`, and no new files other than the ones you were asked for.

## 2. Headings, anchors, chapter numbers

- Chapter headings: `# 2-4 路由钩子` → `# 2-4 Route Hooks`. Appendix headings:
  `# 附录 A · 术语表` → `# Appendix A · Glossary`.
- Chapter ranges keep the same form: `第 1-1–1-7 章` → `Chapters 1-1–1-7`;
  `第 2-4 章` → `Chapter 2-4`; `本章 §5` → `this chapter §5` (keep the `§` section markers).
- **Anchors**: a link fragment such as `../reference/Foundation-Helper.md#注意事项` must point at the
  *English* heading's anchor (`#caveats`). Translate the fragment to match the heading you actually
  wrote in the target file, lower-cased, spaces to hyphens, punctuation dropped (GitHub slug rules).
  If the target file is not translated yet, still use the slug of the heading you would write.

## 3. Voice and punctuation

- Second person, present tense, imperative for instructions: "you", "set the option", "returns".
- Convert CJK punctuation to ASCII: `「」`/`“”` → `"…"`, `（）` → `(…)`, `、` → `, `, `：` → `: `,
  `，` → `, `, `。` → `. `, `——` → ` — `, `～` → `~`. Keep `→` arrows and `⚠️` markers.
- Keep the docs' terse, practical tone. Do **not** pad with marketing language, do not add
  apologies, do not summarize at the end, do not add "Note that…" filler.
- Kept verbatim: emoji/symbols (`⚠️`, `⏳`, `→`), code spans, `//TODO` markers, `§` numbers,
  class/method names, file paths, option keys, `duckphp`/`composer`/`git` command lines.

### Recurring structural phrases (use exactly these)

| Chinese | English |
|---|---|
| `> 解决什么问题：` | `> What this solves:` |
| `> 前置：` | `> Prerequisites:` |
| `> 示例：` | `> Examples:` |
| `> 预计 15 分钟。` | `> About 15 minutes.` |
| `## 最小示例` | `## Minimal example` |
| `## 机制说明` | `## How it works` |
| `## 常见写法` | `## Common patterns` |
| `## 常见错误` | `## Common errors` |
| `## 注意事项` | `## Caveats` |
| `## 下一步` | `## Next steps` |
| `## 方法列表` / `### 公共方法` / `### 受保护方法` | `## Methods` / `### Public methods` / `### Protected methods` |
| `| 现象 | 原因 | 改法 |` | `| Symptom | Cause | Fix |` |
| `参考手册` / `用户指南` | `the reference manual` / `the user guide` |
| `本章` / `上一章` / `下一章` | `this chapter` / `the previous chapter` / `the next chapter` |
| `见第 X-Y 章` | `see Chapter X-Y` |
| `（实测）` | `(measured)` / `(verified)` |
| `示意` | `illustrative` |
| `按需` | `on demand` / `as needed` |

## 4. Glossary (use these renderings consistently)

### Architecture

| Chinese | English |
|---|---|
| 相位 | phase |
| 应用树 | application tree |
| 容器 | container |
| 实例容器 | instance container |
| 共享桶 / 公共桶 | shared bucket |
| 单例 | singleton |
| 可变单例 | mutable singleton |
| 分层（四层） | layering / the four layers |
| 层 | layer |
| 越界（跨层调用） | layer violation |
| 装配 | assembly / to wire up |
| 扩展 / 扩展类 | extension / `Ext\*` class |
| 组件 | component |
| 骨架 | skeleton |
| 脚手架 | scaffold |
| 入口 | entry point |
| 根应用 / 子应用 | root app / child app |
| 挂载 | mount |
| 请求生命周期 | request lifecycle |
| 启动 | boot / bootstrap |

### Layers, routing, controllers

| Chinese | English |
|---|---|
| 控制器 | controller |
| 业务层 | business layer |
| 模型层 | model layer |
| 系统层 | system layer |
| Helper | Helper (do not translate) |
| 路由 | routing |
| 路由钩子 | route hook |
| 钩子链 | hook chain |
| 短路 | short-circuit |
| 中间件 | middleware |
| 洋葱（模型/顺序） | onion (model / order) |
| 视图 | view |
| 视图名 | view name |
| 布局 | layout |
| 模板 | template |
| 动作 / Action | action / Action |
| 会话 | session |
| 事件 | event |
| 异常 | exception |
| 错误处理 | error handling |
| 缓存 | cache |
| 数据库 | database |
| 数据表 | table |
| 事务 | transaction |
| 命令行 | CLI / command line |
| 定时任务 | scheduled task |
| 测试 | test |
| 覆盖率 | coverage |
| 断言 | assertion |
| 用例 | test case |

### Docs and process

| Chinese | English |
|---|---|
| 参考手册 | the reference manual |
| 用户指南 | the user guide |
| 章 / 节 | chapter / section |
| 总目录 | the table of contents |
| 附录 | appendix |
| 术语表 | glossary |
| 代码片段库 | code snippets |
| 迁移对照 | migration cheat sheet |
| 常见错误 | common errors |
| 常见写法 | common patterns |
| 注意事项 | caveats |
| 下一步 | next steps |
| 坑 / 陷阱 | pitfall / trap |
| 取舍（设计取舍） | trade-off (design trade-offs) |
| 约定 | convention |
| 口径 | wording / convention |
| 套路 | recipe |
| 门禁 | gate |
| 死链 | broken link |
| 孤儿页 | orphan page |
| 生成页 | generated page |
| 基线 | baseline |
| 判据 | acceptance criteria / how to verify |
| 实测 | measured / verified by experiment |
| 示意 | illustrative |
| 写法 | pattern / way of writing it |
| 例 / 示例 | example |
| 前置 | prerequisites |
| 现象 | symptom |
| 原因 | cause |
| 改法 | fix |

### Verbs and phrases

| Chinese | English |
|---|---|
| 配（选项） | set (the option) |
| 挂上 / 挂载 | attach / mount |
| 摘掉 | remove / detach |
| 生效 | take effect |
| 不生效 | has no effect |
| 回落 / 回退 | fall back |
| 兜底 | fallback |
| 复用 | reuse |
| 覆盖 | override |
| 重写 | override / rewrite |
| 接管 | take over |
| 替换 | replace |
| 注入 | inject |
| 注册 | register |
| 拦截 | intercept |
| 隔离 | isolate |
| 排查 | troubleshoot |
| 定位 | locate / pin down |
| 复现 | reproduce |
| 绕过 | work around |
| 默认 | default |
| 开箱即用 | works out of the box |
| 主推路数 | the recommended way |
| 不在推荐路径上 | not on the recommended path |

### Do not translate

Class names, method names, option keys, file paths, constants (`EXT_*`), `ext`, `Helper`,
`Show`/`Show302`, `dumpAllRouteHooksAsString()`, `duckphp`, `composer`, package names,
`docs/zh/...` paths when used as paths, and anything inside code spans/fences that is an identifier.

## 5. Files that are generated, not translated

These five reference pages are **produced by `docs/scripts/gen-options-docs.php`**, never translated
or edited by hand:

    docs/en/reference/index.md
    docs/en/reference/options.md
    docs/en/reference/options-by-class.md
    docs/en/reference/options-index.md
    docs/en/reference/setting.md

Their English text comes from the generator's language pack (`--lang=en`), and their option
descriptions are read from the English reference pages. Do not add them to a translation batch.

## 6. Verification (run before you report a file as done)

```bash
# no CJK left (except deliberate sample strings inside fences)
python3 docs/scripts/check-en-docs.py --cjk docs/en/guide/<file>.md
# links resolve inside the English tree, tables and code fences match the Chinese twin
python3 docs/scripts/check-en-docs.py docs/en/guide/<file>.md
```

Report, per file: the target path, whether the CJK check is clean, and any place where the Chinese
source looked **wrong or stale** (that is part of the review: do not silently "fix" it — write it
in your report so it can be fixed in the Chinese tree first, then mirrored).
