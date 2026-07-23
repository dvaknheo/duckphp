# DuckPhp\GlobalAdmin\GlobalAdminTrait

管理员全局组件 Trait。

## 简介

`GlobalAdminTrait` 提供了 `GlobalAdmin` 类的事件、服务委托和视图融合功能。`GlobalAdmin` 通过 `use GlobalAdminTrait;` 引入这些方法。

## 方法

### 服务委托

| 方法 | 说明 |
|---|---|
| `service()` | 返回管理员服务实例。调用 `localService()` 获取本地服务后，通过 `PhaseProxy` 创建跨 Phase 代理 |
| `localService()` | 返回本地管理员服务实例。默认抛出 `AdminException("No Impelment")`，子类可重写 |

### 事件系统

| 方法 | 说明 |
|---|---|
| `on($event, $callback)` | 注册事件监听。事件名前缀为 `GlobalAdmin::` |
| `fire($event, ...$args)` | 触发事件 |

### 权限与审计

| 方法 | 说明 |
|---|---|
| `checkAccess($class, $method, $url)` | 检查当前管理员权限，委托给 `localService()->doCheckAccess()` |
| `isSuper(): bool` | 判断是否超级管理员，委托给 `localService()->doIsSuper()` |
| `log($string, $type)` | 记录操作日志，委托给 `localService()->doLog()` |

### 视图融合

| 方法 | 说明 |
|---|---|
| `getHeaderFooterData($input): array` | 返回 `['header' => '', 'footer' => '']`，子类可重写以添加管理员界面头尾 |
| `mergeView($data, $with_set_head_foot, $header, $footer): array` | 融合管理员视图数据。在 Phase 间切换获取头尾数据，可选设置 View 的头尾 |

```php
// mergeView 内部逻辑
public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array
{
    $phase = App::Phase();
    $last_phase = App::_()->getLastPhase();

    $admin_view = $this->getHeaderFooterData($data);     // 在管理员 Phase 下获取头尾
    App::Phase($last_phase);                              // 切回上层 Phase
    $data['admin_view'] = $admin_view;

    if ($with_set_head_foot) {
        View::_()->setViewHeadFoot($header, $footer);
    }
    App::Phase($phase);
    return $data;
}
```

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
