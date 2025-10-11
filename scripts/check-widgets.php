#!/usr/bin/env php
<?php
// Lightweight guardrail: checks widget Blade files for balanced <div> tags
// and asserts core markers for Quicklinks widget.

function fail($msg) {
    fwrite(STDERR, "[WIDGET-CHECK] ERROR: $msg\n");
    exit(1);
}

$root = __DIR__ . '/..';
$widgetsDir = $root . '/resources/views/widgets';

if (!is_dir($widgetsDir)) {
    fail("Fant ikke widgets-katalogen: $widgetsDir");
}

$errors = [];
$files = glob($widgetsDir . '/*.blade.php') ?: [];

foreach ($files as $file) {
    // Skip backups
    if (preg_match('/\.(bak|backup)\.blade\.php$/i', $file)) {
        continue;
    }
    $contents = file_get_contents($file);
    // Remove Blade and HTML comments
    $contents = preg_replace('/\{\{\-\-.*?\-\-\}\}/s', '', $contents);
    $contents = preg_replace('/<!--.*?-->/s', '', $contents);

    preg_match_all('/<\s*div(?=[\s>])/i', $contents, $open);
    preg_match_all('/<\s*\/\s*div\s*>/i', $contents, $close);

    $openCount = count($open[0] ?? []);
    $closeCount = count($close[0] ?? []);

    if ($openCount !== $closeCount) {
        $errors[] = sprintf(
            '%s -> Åpne <div>: %d, Lukk </div>: %d',
            basename($file), $openCount, $closeCount
        );
    }
}

// Quicklinks integrity markers
$ql = $widgetsDir . '/tools-quicklinks.blade.php';
if (!file_exists($ql)) {
    $errors[] = 'Mangler tools-quicklinks.blade.php';
} else {
    $c = file_get_contents($ql);
    $markers = [
        'toolsQuicklinks()',
        'Hurtiglenker',
        'widget-body',
        '<ul',
        'x-for="link in links"',
    ];
    foreach ($markers as $m) {
        if (strpos($c, $m) === false) {
            $errors[] = "Quicklinks mangler markør: $m";
        }
    }
}

if ($errors) {
    fwrite(STDERR, "\n[WIDGET-CHECK] Fant problemer:\n - " . implode("\n - ", $errors) . "\n\n");
    exit(1);
}

fwrite(STDOUT, "[WIDGET-CHECK] OK: Alle widgets ser strukturelt gyldige ut og Quicklinks er intakt.\n");
exit(0);
