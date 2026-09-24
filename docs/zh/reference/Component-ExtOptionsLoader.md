# DuckPhp\Component\ExtOptionsLoader

把需要跨进程/长期保存的运行期选项（如 is_debug、installed、database/redis 选择等）持久到一个 JSON 文件（默认 `runtime/DuckPhpData.config.json`），并在 root / phase 各侧“bump”进当前 app options。

## 简介

`ExtOptionsLoader`处理一类“要记得、要能在下次跑时仍生效”的动态选项（Web 化 CLI 常见如 `php xx debug --on / --off`）。

工作流：

- root（根应用）初始化时 `loadAllOptions()`：读入 `runtime/DuckPhpData.config.json`（可缺省空）到 `all_ext_options`；
- 以“phase”为键组织数据：`app options data 及其（允许）键`, 通过 `root_get_options_by_phase($phase)` 取当前 Phase 段；
- `bumpOptions($ext_options)`：把取到的段直接写 `$app->options['data']`，并按 `data_file_bump_keys / prefix_keys` 规则回写主 options（如 `is_debug`、`installed`、`redis`、`database` 及 `redis_*` / `database_*` 前缀等）；
- `saveExtOptions($options)`：把一段新 options（附 `__class__` + 时间）经 root 写回 JSON，再立刻 bump 生效。

入口语义由 `DuckPhp` 组件在 `data_file_enable` 时装配：`Component\ExtOptionsLoader`(DuckPhp/DuckPhp 内 Reg 见其 initComponents)。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class ExtOptionsLoader extends ComponentBase`

## 选项

`ExtOptionsLoader::$options`：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `data_file_enable` | true | 是否启用持久外置 options 文件机制（调用装配的上层再决定）。 |
| `data_file_json_file` | `'DuckPhpData.config.json'` | 数据文件名（相对 root runtime 或绝对）。 |
| `data_file_bump_allowed` | true | bump 时是否允许把段写回 app options。 |
| `data_file_bump_keys` | `['installed'=>true,'redis'=>true,'database'=>true,'local_redis'=>true,'local_database'=>true]` | 允许直接整体回写的这些键。 |
| `data_file_bump_prefix_keys` | `['redis_'=>true,'database_'=>true]` | 这些前缀下的任意键会被回写。 |

## 使用方式

```php
use DuckPhp\Component\ExtOptionsLoader;

ExtOptionsLoader::_()->init([], App::_());
// 取外置一段并 bump 生效，例如某命令里面：
ExtOptionsLoader::_()->saveExtOptions(['is_debug'=>false]);
```

典型上层（DuckPhp 根）在 data_file_enable 后会在 root 初始化自己的外置 options，然后依次 bump 到 phases。

## 注意事项

- `saveExtOptions()` 在 root 相位里会检查 `data_file_enable`：若 root 组件未开启 `data_file_enable` 则抛 `DuckPhpSystemException("must enable 'data_file_enable' in root!")`（注意检查的是 **root** 组件自身的选项，不是当前实例的）。
- JSON 文件的默认位置是 `runtime/DuckPhpData.config.json`（root `path_runtime`）；缺失即视为空对象，首次 save 时补 `__date__`。
- 根/子区分用 App::Phase/A Root 语义；各 Phase 读自己段。
- bump 规则是白名单而非全量；避免毒化别的 options（只有 bump_keys/prefix 允许的才写 App options 主表，另外整体写 `data`）。
- 若不需要持久，可把 `data_file_bump_allowed` / 上层 data_file_enable=false，则完全走 options 直配。

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
父 init：处 root 先 loadAllOptions；随后取“本 phase”段的 ext_options 并 bumpOptions。

    public function bumpOptions(array $ext_options): void
若 bump_allowed（和 ext 非空）则把段 copy 进 app->options[data]，并按 bump 键/前缀抄到主 options。

    public function saveExtOptions(array $options): void
在当前 phase 下把这段（附 __class__）交给 root（root_set…&写 JSON），然后对自己 bump。

### 受保护方法

    protected function getRoot()
返回根 app 所属的那个 ExtOptionsLoader 实例（切换 phase 安全）。

    protected function loadAllOptions(): void
读数据 JSON 到 all_ext_options（缺=>null）。

    protected function saveAllOptions(): void
写 all_ext_options(+__date__) 回 JSON 文件、clearstatcache。

    protected function get_ext_options_file(): string
组合 runtime 相对（或绝对）数据文件路径。

    protected function root_get_options_by_phase(string $phase): array
从 all_ext_options 取某 phase 段（缺空）。

    protected function root_set_options_by_phase(string $phase, array $options): void
把 options 写进 all_ext_options[$phase]。

## 相关链接

- [DuckPhp\DuckPhp](DuckPhp.md) (data_file_enable 承载)
- [DuckPhp\Core\App](Core-App.md) 若多层需要 is_debug 由命令改
- `Component\Command::command_debug`（写数据文件开关）
