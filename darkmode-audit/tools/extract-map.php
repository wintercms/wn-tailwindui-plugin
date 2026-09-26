<?php
/**
 * Capture the currently-applied rewrites as a replayable map.
 *
 * The original appliers inferred each colour's role from selector/property
 * heuristics. Those heuristics were tuned interactively and are not worth
 * re-deriving: the result of that tuning is already sitting in the working tree
 * as `var(--wn-token, #originalhex)` at 661 sites, and the fallback preserves
 * the literal it replaced. Reading the tree back is therefore both simpler and
 * strictly more faithful than re-running the inference -- it cannot drift from
 * what was actually built and reviewed.
 *
 * Run with the rewrites APPLIED. Writes token-map.json.
 */
require __DIR__ . '/lab.php';
$root = coreRoot();

$map = [];
$files = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/modules'));
foreach ($it as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) !== 'less') { continue; }
    $rel = str_replace($root . '/', '', $f->getPathname());
    $lines = file($f->getPathname(), FILE_IGNORE_NEW_LINES);
    $hit = false;
    foreach ($lines as $i => $line) {
        if (!preg_match_all('/var\(\s*(--wn-[a-z0-9-]+)\s*,\s*(#[0-9a-fA-F]{3,8})\s*\)/', $line, $m, PREG_SET_ORDER)) {
            continue;
        }
        foreach ($m as $mm) {
            $map[] = ['file' => $rel, 'line' => $i + 1, 'token' => $mm[1], 'hex' => $mm[2]];
        }
        $hit = true;
    }
    if ($hit) { $files++; }
}

usort($map, fn ($a, $b) => [$a['file'], $a['line']] <=> [$b['file'], $b['line']]);
file_put_contents(__DIR__ . '/token-map.json', json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$tokens = array_unique(array_column($map, 'token'));
printf("captured %d rewrites across %d files, %d distinct tokens\n", count($map), $files, count($tokens));
printf("wrote %s\n", basename(__DIR__) . '/token-map.json');
