# Deployment

DuckPHP applications can be deployed on any web server that supports PHP. This chapter introduces common deployment methods and considerations.

## Production Environment Configuration

### Disable Debug Mode

Debug mode must be disabled in production to avoid leaking sensitive information:

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

### Use Settings File

Put sensitive configurations such as database and Redis in `config/DuckPhpSettings.config.php`, do not hardcode them in the `App` class:

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

### Log Configuration

It is recommended to enable exception logging in production:

```php
public $options = [
    'default_exception_do_log' => true,
    'path_runtime' => 'runtime',
];
```

## Directory Permissions

Before deployment, ensure the following directories are writable:

| Directory | Purpose | Permission |
|---|---|---|
| `runtime/` | Logs, cache, temporary files | Writable |
| `runtime/logs/` | Log files | Writable |
| `public/` | Web entry | Readable |

```bash
chmod -R 755 runtime
chmod -R 777 runtime/logs  # Or configure according to web server user
```

## Web Server Configuration

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

Use `.htaccess` file:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

Make sure Apache has the `mod_rewrite` module enabled.

## Using PHP Built-in Server for Development

The development environment can use PHP's built-in server:

```bash
php -S localhost:8080 -t public
```

Or use the framework CLI:

```bash
php vendor/bin/duckphp run
```

## Docker Deployment

### Dockerfile Example

```dockerfile
FROM php:8.1-fpm

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy project
COPY . /var/www/project
WORKDIR /var/www/project

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data runtime

EXPOSE 9000
CMD ["php-fpm"]
```

### docker-compose.yml Example

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

## Deployment Checklist

- [ ] Disable `is_debug`
- [ ] Configure correct `error_404` and `error_500` views
- [ ] Use settings files or environment variables for database, Redis, and other sensitive information
- [ ] `runtime/` directory is writable
- [ ] Web server root points to `public/`
- [ ] Deny access to sensitive files such as `.git`, `.env`, and configuration files
- [ ] Enable HTTPS (recommended for production)
- [ ] Configure log rotation to avoid oversized log files

## FAQ

### Q: 404 error after deployment
A: Check whether the web server is configured for URL rewriting, ensuring all requests are forwarded to `public/index.php`.

### Q: Insufficient permissions causing logs to fail to write
A: Ensure the `runtime/` directory is writable by the web server user. You can set it with `chown -R www-data:www-data runtime`.

### Q: Static resources 404
A: Check the `controller_resource_prefix` configuration, ensure Nginx/Apache can correctly map resource paths.

### Q: Sub-application inaccessible after deployment
A: Confirm that the sub-application's `controller_url_prefix` matches the web server's routing configuration.
