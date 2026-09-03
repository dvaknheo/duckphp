# DuckPhp\Component\Configer

从 `config/` 读取 PHP 配置文件的极简组件：按“基名”require、缓存结果、支持取整/单键。

## 简介

`Configer extends ComponentBase` 负责把 `{file_basename}.php`（返回数组）读入内存并缓存（`$all_config`）。

- `_Config($file_basename,$key,$default)`：读整个或单键；
- 单文件缺失返回 `[]` / default，不抛错；
- 文件后缀 `.php` 由内部补；目标相对 `path_config`（相对项目根），存储 `extendFullFile`（因此会参与 Phase 子应用覆盖）。

业务层一般不直接 Command，而用随各层 Helper（如 Business/Controller 的 `Config`）最终经它；但 Doc 面向其方法本身。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class Configer extends ComponentBase`

## 选项

`Configer::$options`:

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根（用于把相对 config 拼绝对）。 |
| `path_config` | `'config'` | 配置目录名（相对 `path`，可给绝对覆盖）。 |

## 使用方式

```php
use DuckPhp\Component\Configer;

$c = Configer::_()->init(['path'=>__DIR__,'path_config'=>'config']);

$all = $c->_Config('app');              // 返回 config/app.php 内容
$val = $c->_Config('app','debug',false); // 取单键
```

config/app.php 例如 `return [ 'debug'=>true, 'db'=>... ];`。

## 注意事项

- 缓存基于 basename：同 basename 第二次不会重复 require。
- 目录完全缺文件 → 空数组；没有异常。
- 子应用用相同文件可覆盖：extendFullFile 查找能命中（phase 优先）。

## 方法列表

### 公共方法

    public function _Config($file_basename = 'config', $key = null, $default = null)
读某配置：无 key 返回整块（空→default）；有 key 返回 `$config[$key] ?? $default`（经 _LoadConfig 缓存）。

### 受保护方法

    protected function _LoadConfig(string $file_basename): array
已缓存则直接；否则补 `.php`、经 `extendFullFile(path, path_config, file)` 定位并 require，成功即写 all_config 缓存。缺失存 [] 并返回 []。

    protected function loadFile(string $file): array
`return require $file;`，实际读文件为数组。

（另有继承于 ComponentBase 的 init/`_()` 等不下表。）

## 相关链接

- [DuckPhp\Core\App](Core-App.md)（把 Configer 预注册等）
- `Foundation` 各层 Helper 里 Config 走此
- config 目录约定见 Project 结构指南
