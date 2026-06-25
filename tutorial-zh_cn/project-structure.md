# 项目结构

## 业务项目目录结构

对于业务开发者，通常只需要关心以下目录：

```
project/
├── config/
│   └── DuckPhpSettings.config.php  # 运行时配置（数据库等）
├── public/
│   └── index.php                   # Web 入口（唯一对外文件）
├── src/
│   ├── System/
│   │   └── App.php                 # 继承 DuckPhp 的应用配置
│   ├── Controller/                 # 控制器层
│   ├── Business/                   # 业务层
│   └── Model/                      # 模型层
├── view/                           # 视图模板
├── runtime/                        # 运行时（日志、缓存等）
└── cli.php                         # CLI 命令行入口
```

### 各层职责

| 层 | 命名空间前缀 | 职责 |
|---|---|---|
| `Controller` | `{项目命名空间}\Controller` | HTTP 请求入口，处理输入/输出 |
| `Business` | `{项目命名空间}\Business` | 业务逻辑编排，调用 Model |
| `Model` | `{项目命名空间}\Model` | 数据访问层，表操作 |
| `System` | `{项目命名空间}\System` | 应用配置与生命周期 |
| `view/` | - | PHP 模板文件 |
