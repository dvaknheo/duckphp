# 2-1 四层架构与调用规范

> **本章内容已并入[第 1-3 章 目录结构与四层架构](project-structure.md)** —— 这里保留一个**指路页**：第二卷的章号因此不必重排（合并/拆分章节时最省事），你从卷二任意一章点进来也仍然落在这里。

四层的规矩、越界矩阵、Helper 分层、全局函数、常见错误**全在第 1-3 章**。这里只留一张速查表，方便快速定位：

| 层 | 一句话职责 | 别做 |
|---|---|---|
| Controller | 请求的入口与出口 | 不碰 Model/Db，不写业务规则 |
| Business | 业务编排（**无状态**） | 不读 `$_GET`/`$_POST`/`Session`，要什么由参数传进来 |
| Model | 数据访问 | 不写业务判断、不抛业务异常 |
| View | 只显示 | 不查库、不调 Business |
| System | 接线（**不属于四层**） | 不让四层反向依赖它 |

一句话记住越界矩阵的结论：**控制器不碰 Db/Model、业务不碰请求上下文、视图只读不写**；越界的代价不是报错，而是覆盖、多应用、CLI/测试复用这些能力悄悄失效。

- 完整内容：[第 1-3 章 目录结构与四层架构](project-structure.md)（目录约定、命名规范、五层职责、越界矩阵、违规示例、常见错误）
- 目录结构与命名规范也在同一章。

## 本卷地图

第二卷（2-1–2-20 章）按「先看懂时序 → 再走请求路径 → 再补横切能力 → 最后框架机制与进阶」排：

| 阶段 | 章 | 讲什么 |
|---|---|---|
| 规范 | [**1-3**](project-structure.md) | 四层各管什么、谁不能调谁（内容在卷一，本页只作指路） |
| 时序与请求路径 | 2-2–2-8 | [请求生命周期](lifecycle.md)（含框架默认装了哪些内置组件）→ [路由](routing.md) → [路由钩子](route-hooks.md) → [控制器](controllers.md) → [视图](views.md) → [数据库](database.md) → [模型](model.md) |
| 横切能力 | 2-9–2-11 | [Helper 与全局函数](helper.md)、[表单与验证](validator.md)、[会话](session.md) |
| 框架机制 | 2-12–2-13 | [异常](exception.md)、[事件](events.md) |
| 进阶 | 2-14–2-18 | [缓存](cache.md)、[国际化](i18n.md)、[命令行](cli.md)、[测试](testing.md)、[安全与性能](security-performance.md) |
| 用户 / 管理员体系（用法） | 2-19–2-20 | [使用用户系统](user.md)、[使用管理员系统](admin.md)（自己实现看[第 4-12](impl-user.md)、[4-13 章](impl-admin.md)） |

## 下一步

- [第 2-2 章 请求生命周期](lifecycle.md)：这些层是在什么时候被装配起来的。
- [第 2-3 章 路由进阶](routing.md)：请求怎么落到某个控制器方法。
- [第 2-5 章 控制器](controllers.md)：输入怎么取、输出有哪几种方式。
- 参考手册：[DuckPhp\Foundation\Controller\ControllerHelper](../reference/Foundation-Controller-ControllerHelper.md)、[DuckPhp\Foundation\Business\BusinessHelper](../reference/Foundation-Business-BusinessHelper.md)、四层基类 [Controller\Base](../reference/Foundation-Controller-Base.md) / [Business\Base](../reference/Foundation-Business-Base.md) / [Model\Base](../reference/Foundation-Model-Base.md)。
