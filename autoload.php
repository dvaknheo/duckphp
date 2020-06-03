<?php
require __DIR__.'/src/Core/AutoLoader.php';
spl_autoload_register([DuckPhp\Core\AutoLoader::class ,'DuckPhpSystemAutoLoader']);