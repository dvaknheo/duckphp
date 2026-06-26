# DuckPHP 项目编码规则

## 层级调用规范

```
Controller 层 (可处理请求上下文)
  ├── 可调用: Action, Business, Helper, Session
  └── 禁止直接调用 Model

Business 层 (纯无状态)
  ├── 可调用: Model, Service, Helper
  └── 禁止: 读写 Session, 访问 $_GET/$_POST/$_SERVER

Model 层 (纯无状态)
  ├── 可调用: 仅数据访问相关
  └── 禁止: 业务逻辑, 抛异常

System 层
  └── 处理框架相关调用, 异常定义, 应用配置
```

## 核心原则

1. **Controller/Business/Model 层** 除基础代码外, 请勿直接调用 `DuckPhp` 命名空间下的类。
2. 框架相关调用集中在 **System 层** 处理。
3. Business 层保持**纯无状态**, 可被 CLI、测试、API 等任意环境复用。

## 命名规范

| 类型 | 命名规则 | 示例 |
|------|---------|------|
| 控制器类 | `{Name}Controller` | `UserController` |
| 控制器方法 | `action_{method}` | `action_index()` |
| Business 类 | `{Name}Business` | `UserBusiness` |
| Service 类 | `{Name}Service` | `CommonService` |
| Model 类 | `{Name}Model` | `UserModel` |
| Action 类 | `{Name}Action` | `UserAction` |
| Session 类 | `Session` | `Session` |
| 异常类 | `{Name}Exception` | `ProjectException` |

## 文件组织

```
src/
├── Controller/          # HTTP 请求入口
├── Business/            # 业务逻辑
├── Model/               # 数据访问
└── System/              # 应用配置与异常
```
