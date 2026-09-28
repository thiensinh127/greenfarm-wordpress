<?php
/**
 * Parse every plugin PHP file using the active Playground PHP runtime.
 *
 * @package GreenFarmCore
 */

declare(strict_types=1);

$root     = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$count    = 0;

try {
    foreach ($iterator as $file) {
        if (! $file->isFile() || 'php' !== $file->getExtension()) {
            continue;
        }

        token_get_all((string) file_get_contents($file->getPathname()), TOKEN_PARSE);
        ++$count;
    }
} catch (ParseError $error) {
    fwrite(STDERR, 'PHP parse failure: ' . $error->getMessage() . "\n");
    exit(1);
}

echo "Parsed {$count} plugin PHP files with no syntax errors.\n";
