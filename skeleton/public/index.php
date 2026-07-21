<?php declare(strict_types=1);
/**
 * DuckPhp
 * Do not change me.
 */
foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}
\YourProjectName\System\App::RunQuickly([
    //
]);

