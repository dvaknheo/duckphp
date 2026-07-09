# 应用选项总览

DuckPhp 的选项通过 `$options` 属性传入，最终合并到 `DuckPhp\Core\App::$options` 中。
不同来源的类会定义自己的默认选项，常见来源包括：

- **入口类**：`DuckPhp\DuckPhp`
- **核心初始化**：`DuckPhp\Core\KernelTrait`
- **核心组件**：`DuckPhp\Core\App`、`DuckPhp\Core\Route` 等
- **自带组件**：`DuckPhp\Component\DbManager`、`DuckPhp\Component\Lang` 等
- **可选扩展**：`DuckPhp\Ext\*` 下的各类
- **安装器与辅助**：`DuckPhp\FastInstaller\*`、`DuckPhp\HttpServer\HttpServer` 等

## 浏览方式

| 页面 | 说明 |
|---|---|
| [options-by-class.md](options-by-class.md) | 按来源类分组列出所有选项，适合了解某个组件支持哪些配置。 |
| [options-index.md](options-index.md) | 按选项名字母顺序列出所有选项，适合快速查找某个选项的含义。 |

## 配置建议

- 加粗选项通常由 `DuckPhp\DuckPhp` 或 `DuckPhp\Core\App` 默认提供。
- 未加粗选项一般来自具体组件，需要通过 `ext` 选项加载该组件，或在初始化时手动开启。
- 大量选项的默认值会根据设置文件（`DuckPhpSettings.config.php`）自动重新加载。
