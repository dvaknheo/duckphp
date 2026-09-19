# 用户指南重写 · 进度 Checklist

> 配套阅读：[用户指南重写 · 交接与维护指南](guide-maintenance-guide.md) —— 那份讲**怎么做**（目标、约束、写作模板、校验命令、已知坑），本文件只管**做到哪了**。
>
> **勾选纪律**：一章只在「按模板重写完 + 示例实跑过 + 校验命令通过」之后，才把 ⏳/📝 改成 ✅ 并勾选；只改错别字、只调格式不算完成。
>
> 图例：**✅ 已完成** ｜ **📝 文件在，内容仍旧，待改写** ｜ **⏳ 未写**

## 里程碑

- [x] **M0** 一页总目录 + 站点首页瘦身 + 附录 A 术语表 + `ZThirdDemo` 示例工程与冒烟测试
- [x] **M1** 第三卷 26–32（7 章，含示例被测试兜底）
- [ ] **M2** 第二卷 8–25（18 章：12 篇改写 + 6 篇新写）
- [x] **M3** 第一卷 1–7（2 篇新写 + 5 篇改写）
- [ ] **M4** 第四卷 33–42 + 附录 B/C/D；并配合参考手册侧两页迁入后的链接收尾
- [ ] **M5** 收尾：删掉被吸收的 `architecture.md` / `components.md` 与 guide 侧两篇附录；`reference/index.md` 登记；全量校验

---

## 第一卷 · 入门 —— ✅ 已完成

- [x] 1 DuckPHP 是什么 —— `intro.md`（含与 Laravel/Yii2/CI 的定位对比）
- [x] 2 安装与最小示例 —— `install.md`（两条安装路线 + 3 文件起步）
- [x] 3 目录结构与编码规则 —— `project-structure.md`（含四层调用铁律矩阵）
- [x] 4 第一个页面 —— `quickstart.md`（便签列表，五层走通）
- [x] 5 配置与设置 —— `configuration.md`（options vs settings 的分工与白名单坑）
- [x] 6 调试、日志与 CLI 初体验 —— `debugging.md`
- [x] 7 上线最小清单 —— `deployment.md`（含内置服务器 router 写法，实测过）
- [x] 本卷收尾校验：`docs/zh` 站内链接 **1005 条 0 死链**、`docs/zh` 153 篇 md 全 UTF-8、7 章 **101–226 行**（≤400）、全量 `php vendor/bin/phpunit --no-coverage` → **`OK (92 tests, 556 assertions)`**

## 第二卷 · 单一应用

- [ ] 8 请求生命周期与钩子点 —— `lifecycle.md` 📝（拆薄：会话→17、事件→20、调试→6）
- [ ] 9 路由进阶 —— `routing.md` 📝
- [ ] 10 控制器 —— `controllers.md` ⏳（从 `layers.md` 抽出）
- [ ] 11 视图与模板 —— `views.md` ⏳（从 `layers.md` 抽出）
- [ ] 12 数据库 —— `database.md` 📝
- [ ] 13 模型层 —— `model.md` 📝
- [ ] 14 四层架构与调用规范 —— `layers.md` 📝（保留文件名，正文指向 10/11/13）
- [ ] 15 Helper 与全局函数 —— `helper.md` 📝（含修 `assignRewrite` 的示例）
- [ ] 16 表单与验证 —— `validator.md` 📝
- [ ] 17 会话与用户/管理员体系 —— `external-auth.md` 📝（含修旧键名 `user_callback_get_*`）
- [ ] 18 中间件与钩子链 —— `middleware.md` ⏳（≤400 行）
- [ ] 19 异常与错误处理 —— `exception.md` 📝（补「系统异常只内部用」）
- [ ] 20 事件系统 —— `events.md` ⏳（≤400 行）
- [ ] 21 缓存与 Redis —— `cache.md` ⏳（≤400 行）
- [ ] 22 国际化与文案 —— `i18n.md` ⏳（≤400 行）
- [ ] 23 命令行与定时任务 —— `cli.md` 📝
- [ ] 24 测试 —— `testing.md` 📝
- [ ] 25 安全与性能清单 —— `security-performance.md` ⏳（≤400 行）

## 第三卷 · 使用第三方应用 —— ✅ 已完成

- [x] 26 应用树与相位基础 —— `advanced-phase.md`
- [x] 27 把外部应用挂进来 —— `mount-app.md`
- [x] 28 静态资源与文档根 —— `static-resources.md`
- [x] 29 组件共享与应用间通信 —— `component-sharing.md`
- [x] 30 重写与覆盖 —— `overriding.md`
- [x] 31 安装器与 Web 安装流程 —— `installer.md`
- [x] 32 综合实战：前台 + 后台 + API —— `case-multi-app.md`
- [x] 示例工程 `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php`（36 断言）

## 第四卷 · 高级话题

- [ ] 33 容器与相位内部机制 —— `container-phases.md` ⏳（吸收 `architecture.md` 组件层）
- [ ] 34 开发组件与扩展 —— `custom-component.md` ⏳（吸收 `components.md` 扩展节）
- [ ] 35 替换框架行为 —— `replace-behavior.md` ⏳
- [ ] 36 无 Composer·单文件·内嵌 —— `embed.md` ⏳
- [ ] 37 常驻进程与内嵌 HTTP —— `http-server.md` ⏳
- [ ] 38 多入口·多域名·多 SAPI —— `multi-entry.md` ⏳
- [ ] 39 测试基建与覆盖率流水线 —— `coverage.md` ⏳
- [ ] 40 文档与参考手册维护 —— `doc-maintenance.md` ⏳（链到两份维护指南）
- [ ] 41 性能调优与排错手册 —— `troubleshooting.md` ⏳
- [ ] 42 设计取舍与已知坑 —— `design-notes.md` ⏳

## 附录

- [x] A 术语表 —— `appendix-glossary.md`
- [ ] B 代码片段库 —— `appendix-snippets.md` ⏳
- [ ] C 从 Yii2 / CodeIgniter / Laravel 迁移 —— `appendix-migration.md` ⏳
- [ ] D FAQ 与排错索引 —— `appendix-faq.md` ⏳

---

## 参考手册侧（作者在新对话完成，本任务只等结果）

- [ ] 建 `docs/zh/reference/GlobalFunctions.md`（内容取自 `docs/zh/guide/appendix-global-functions.md`）
- [ ] 重建 `docs/zh/reference/options.md` / `options-by-class.md` / `options-index.md`（顺手消灭其中的 `class_admin`/`class_user`、`FastInstaller` 残留；内容取自 `docs/zh/guide/appendix-options.md`）
- [ ] 两页建好后，在本任务侧收尾：
  - [ ] 删除 `docs/zh/guide/appendix-global-functions.md` 与 `docs/zh/guide/appendix-options.md`
    （原文可取：`git show <提交>:docs/zh/guide/appendix-options.md`）
  - [ ] 把 `docs/zh/guide/configuration.md` 的附录链接改成精确目标
  - [ ] 把 `docs/zh/guide/index.md` 底部那行「已归入参考手册」补成精确链接

## 待决策（勾掉即已定，定完请写进交接指南对应节）

- [ ] **Q1 写作顺序**：先做第二卷 8–25？
- [ ] **Q2 长章拆解**：`architecture.md`（532 行）拆给「第 1 章 + 第 33 章 + 第 8 章时序图」后删除；`layers.md`（581 行）保留文件名、只留四层规范
- [ ] **Q3 `skeleton/` 的失真内容**（`agent-zh.md` 的 `class_user`、`RULES.md` 的方法前缀默认值）怎么处理
- [ ] **Q4 新发现的源码残留**：`DuckPhp::_Show()` 里 `$view === '' ? … : $view;` 这个没赋值的死表达式清不清

## 每轮收尾自检（每卷结束时勾一遍）

- [x] `python3 scripts/check-doc-links.py docs` → `docs/zh` 0 坏链（第一卷收尾实测：1005 条 0 死链；`docs/old/`、`docs/en/index.md` 的历史死链不属本任务）
- [x] 新增/修改的 md 全是 UTF-8（无 GBK）（`docs/zh` 153 篇逐字节校验通过；早前 `reference/index.md` 的 20 字节乱码，已随作者重写该页消失）
- [x] 本卷新写的章都 ≤400 行（第一卷 101–226 行）
- [x] 改过的示例都在 WSL 里实跑通过（第三卷 → `tests/ZThirdDemoTest.php` 36 断言；第一/二卷的兜底是 `demo/`，其 `ZAllDemoTest` 的 `files` 期望长度已按当前源码对齐为 **10360**）
- [x] 全量 `php vendor/bin/phpunit --no-coverage` 通过（当前基线 `OK (92 tests, 556 assertions)`）
- [x] 本文件的复选框与状态图例已更新
