<?php declare(strict_types=1);
/**
 * tests/genoptions.php - legacy entry point, kept so old habits and docs
 * (`php tests/genoptions.php`) keep working.
 *
 * The generator itself now lives in scripts/gen-options-docs.php, which writes the
 * option pages of the reference manual (options-by-class / options-index /
 * index / options / setting) and supports --check.
 *
 *   php tests/genoptions.php            # same as: php scripts/gen-options-docs.php
 *   php tests/genoptions.php --check    # verify only, exit 1 when stale
 *
 * The old DocFixer/OptionsGenerator implementation was removed: it had been
 * paused (`return;`) for a long time and still referenced classes that no
 * longer exist (e.g. DuckPhp\FastInstaller\*).
 */

require __DIR__ . '/../scripts/gen-options-docs.php';
