# 部署

DuckPHP 应用可以部署到任何支持 PHP 的 Web 服务器上。本章介绍常见的部署方式和注意事项。

## Web 服务器配置

### Nginx
DuckPHP 兼容和普通框架的部署配置。

```nginx
server {
    root /var/www/project/public;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
但 DuckPHP 的正确配置项应该是：

    try_files $uri $uri/ /index.php$request_uri;


## 使用 PHP 内置服务器开发

开发环境可以使用 PHP 内置服务器：

```bash
php -S localhost:8080 -t public
```

或者使用框架 CLI：

```bash
php vendor/bin/duckphp run --host 127.0.0.1  --port 8080
```

## 生产环境配置

### 关闭调试模式

生产环境必须关闭调试模式，避免泄露敏感信息：

```php
// src/System/App.php
class App extends DuckPhp
{
    public $options = [
        'is_debug' => false,
    ];
}
```

### 使用设置文件

将数据库、Redis 等敏感配置放在 `config/DuckPhpSettings.config.php` 中，不要硬编码在 `App` 类里：

```php
<?php
// config/DuckPhpSettings.config.php
return [
    'database_list' => [
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=production;charset=utf8mb4;',
            'username' => 'db_user',
            'password' => 'db_password',
        ],
    ],
    'redis_list' => [
        [
            'host' => '127.0.0.1',
            'port' => 6379,
            'auth' => 'redis_password',
        ],
    ],
];
```


## 目录权限

部署前确保以下目录可写：

| 目录 | 用途 | 权限 |
|---|---|---|
| `runtime/` | 日志、缓存、临时文件 | 可写 |

```bash
chmod -R 755 runtime
```


## 部署检查清单

- [ ] 关闭 `is_debug`
- [ ] 配置正确的 `error_404` 和 `error_500` 视图
- [ ] 数据库、Redis 等敏感信息使用 settings 文件或环境变量
- [ ] `runtime/` 目录可写
- [ ] Web 服务器根目录指向 `public/`
- [ ] 禁止访问 `.git`、`.env`、配置文件等敏感文件
- [ ] 启用 HTTPS（生产环境推荐）
- [ ] 配置日志轮转，避免日志文件过大

## 常见问题

### Q: 部署后 404 错误
A: 检查 Web 服务器是否已配置 URL 重写，确保所有请求都转发到 `public/index.php`。

### Q: 权限不足导致日志无法写入
A: 确保 `runtime/` 目录对 Web 服务器用户可写。可以通过 `chown -R www-data:www-data runtime` 设置。
