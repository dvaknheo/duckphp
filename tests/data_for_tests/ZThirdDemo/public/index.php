<?php declare(strict_types=1);
/**
 * ZThirdDemo - web entry of the main app.
 */
foreach ([__DIR__ . '/../../../../vendor/autoload.php', __DIR__ . '/../../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}
// this demo keeps both apps inside tests/, so tell the autoloader where to look
\DuckPhp\Core\AutoLoader::_()->init([
    'path' => __DIR__ . '/../src/',
    'namespace' => 'ZThirdDemo',
    'path_namespace' => '',
])->run();
\DuckPhp\Core\AutoLoader::_()->assignPathNamespace(__DIR__ . '/../third/', 'ZThirdDemo\Third');

\ZThirdDemo\System\MainApp::RunQuickly([]);
