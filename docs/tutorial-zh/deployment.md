# 部署

DuckPHP 应用可以部署到任何支持 PHP 的 Web 服务器上。本章介绍常见的部署方式和注意事项。

## 生产环境配置

### 关闭调试模式

生产环境必须关闭调试模式，避免泄露敏感信息：

```php
// src/System/App.php
class App extends DuckPhp
{
    public $options = [
        'is_debug' => false,
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
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

### 日志配置

生产环境建议开启异常日志：

```php
public $options = [
    'default_exception_do_log' => true,
    'path_runtime' => 'runtime',
];
```

## 目录权限

部署前确保以下目录可写：

| 目录 | 用途 | 权限 |
|---|---|---|
| `runtime/` | 日志、缓存、临时文件 | 可写 |
| `runtime/logs/` | 日志文件 | 可写 |
| `public/` | Web 入口 | 可读 |

```bash
chmod -R 755 runtime
chmod -R 777 runtime/logs  # 或者根据 web 服务器用户配置
```

## Web 服务器配置

### Nginx

```nginx
server {
    listen 80;
    server_name example.com;
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

    location ~ /\.(git|svn|env|md) {
        deny all;
    }
}
```

### Apache

使用 `.htaccess` 文件：

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

确保 Apache 已启用 `mod_rewrite` 模块。

## 使用 PHP 内置服务器开发

开发环境可以使用 PHP 内置服务器：

```bash
php -S localhost:8080 -t public
```

或者使用框架 CLI：

```bash
php vendor/bin/duckphp run
```

## Docker 部署

### Dockerfile 示例

```dockerfile
FROM php:8.1-fpm

# 安装扩展
RUN docker-php-ext-install pdo pdo_mysql

# 复制项目
COPY . /var/www/project
WORKDIR /var/www/project

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

# 设置权限
RUN chown -R www-data:www-data runtime

EXPOSE 9000
CMD ["php-fpm"]
```

### docker-compose.yml 示例

```yaml
version: '3'
services:
  web:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - .:/var/www/project
  php:
    build: .
    volumes:
      - .:/var/www/project
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: project
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

### Q: 静态资源 404
A: 检查 `controller_resource_prefix` 配置，确保 Nginx/Apache 能正确映射资源路径。

### Q: 子应用部署后无法访问
A: 确认子应用的 `controller_url_prefix` 与 Web 服务器的路由配置一致。
