# DuckPhp\Component\ExtOptionsLoader

`DuckPhp\Component\ExtOptionsLoader` 是一个额外数据文件加载组件。它从 JSON 文件读取各应用（或各相位）的扩展选项，并将需要的数据 `bump` 到当前应用的 `$options` 中。该组件常用于安装器、子应用配置或运行时数据持久化场景。

---

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `data_file_enable` | `true` | 是否启用额外数据文件加载。 |
| `data_file_json_file` | `'DuckPhpData.config.json'` | JSON 文件名。默认放在 `runtime/` 目录下。 |
| `data_file_bump_allowed` | `true` | 是否允许将加载的数据 `bump` 到应用选项。 |
| `data_file_bump_keys` | `['installed' => true, 'redis' => true, 'database' => true, 'local_redis' => true, 'local_database' => true]` | 需要直接 `bump` 到应用选项顶层的关键字映射。键为选项名，值为是否启用。 |
| `data_file_bump_prefix_keys` | `['redis_' => true, 'database_' => true]` | 需要按前缀 `bump` 到应用选项顶层的前缀映射。键为前缀，值为是否启用。 |

### 全部选项

```php
public $options = [
    'data_file_enable' => true,
    'data_file_json_file' => 'DuckPhpData.config.json',
    'data_file_bump_allowed' => true,
    'data_file_bump_keys' => ['installed' => true, 'redis' => true, 'database' => true, 'local_redis' => true, 'local_database' => true],
    'data_file_bump_prefix_keys' => ['redis_' => true, 'database_' => true],
];
```

---

## 数据文件格式

`ExtOptionsLoader` 读取的 JSON 文件格式如下：

```json
{
    "__date": "2026-07-11 12:00:00",
    "MyApp\\System\\App": {
        "installed": true,
        "database": {
            "dsn": "mysql:host=127.0.0.1;dbname=test",
            "username": "root",
            "password": "root"
        },
        "redis_host": "127.0.0.1",
        "redis_port": 6379
    },
    "OtherApp\\System\\App": {
        "installed": true
    }
}
```

顶层键为应用类名（全限定名），值为该应用的扩展选项。`__date` 是自动写入的更新时间戳，仅用于记录。

---

## 使用方式

### 自动加载

在 `DuckPhp\Core\App` 的 `$options` 中通过 `ext` 配置启用：

```php
public $options = [
    'ext' => [
        DuckPhp\Component\ExtOptionsLoader::class => [],
    ],
];
```

初始化时，`ExtOptionsLoader` 会自动读取 `runtime/DuckPhpData.config.json`，并将当前应用类名对应的扩展选项 `bump` 到应用选项中。

### 手动保存数据

```php
$options = [
    'installed' => true,
    'database' => ['dsn' => 'sqlite:...'],
];
DuckPhp\Component\ExtOptionsLoader::_()->saveData($options);
```

`saveData()` 会：
1. 读取完整 JSON 文件。
2. 合并当前应用类的扩展选项。
3. 写入 `__date` 时间戳。
4. 重新写回 JSON 文件。
5. 调用 `bumpOptions()` 将新数据同步到当前应用选项。

---

## 方法列表

### 公开方法

| 方法 | 说明 |
|---|---|
| `init(array $options, ?object $context = null)` | 初始化组件，加载 JSON 文件并 `bump` 当前应用选项。 |
| `bumpOptions(array $ext_options): void` | 将扩展选项按 `data_file_bump_keys` 和 `data_file_bump_prefix_keys` 规则合并到应用选项。 |
| `saveData(array $options): void` | 保存当前应用的扩展选项到 JSON 文件，并同步 `bump` 到应用选项。 |

### 受保护方法

| 方法 | 说明 |
|---|---|
| `get_ext_options_file(): string` | 计算并返回 JSON 文件的完整路径。 |
| `fill_all_ext_options(string $full_file): void` | 读取 JSON 文件内容并解析到静态 `$all_ext_options`。 |

---

## 注意事项

1. **文件路径**：`data_file_json_file` 默认解析到 `runtime/DuckPhpData.config.json`。如果设置绝对路径，则直接使用。
2. **加载顺序**：`ExtOptionsLoader` 在 `App` 初始化时通过 `ext` 加载。它会先读取 JSON，再用 `bumpOptions` 把数据合并到应用选项。之后初始化的核心组件会看到这些选项。
3. **静态缓存**：`$all_ext_options` 是静态属性，同一进程内只读取一次 JSON 文件，避免重复 I/O。
4. **Bump 规则**：
   - `data_file_bump_keys` 中的键会直接复制到 `App::$options` 顶层。
   - `data_file_bump_prefix_keys` 中的前缀会匹配 JSON 中所有以该前缀开头的键，并复制到 `App::$options` 顶层。
5. **相位子应用**：每个应用类名在 JSON 中有独立的键，因此不同相位或子应用的数据互不干扰。

---

## 相关链接

- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Component\Configer](Component-Configer.md)
