<?php
/**
 * The appliers rewrote only the FIRST literal on each line, so a declaration that
 * repeats a colour -- a checkerboard gradient, a two-sided inset shadow -- ended up
 * half tokenised. A theme override then applies to some stops and not others.
 *
 * For every line that already carries `var(--wn-x, LITERAL)`, rewrite any remaining
 * bare occurrence of that same LITERAL on the line to use the same token.
 */
require __DIR__ . '/lab.php';
$root = coreRoot();

$files = $edits = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/modules'));
foreach ($it as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) !== 'less') { continue; }
    $lines = file($f->getPathname(), FILE_IGNORE_NEW_LINES);
    $changed = false;
    foreach ($lines as $i => $line) {
        if (!preg_match_all('/var\(\s*(--wn-[a-z0-9-]+)\s*,\s*(#[0-9a-fA-F]{3,8})\s*\)/', $line, $m, PREG_SET_ORDER)) {
            continue;
        }
        // Mask every var() call so a literal serving as a fallback is never
        // re-matched, and re-mask after each pass so the calls a pass just created
        // are hidden too. (Iterating once per existing var() without re-masking
        // re-wrapped the literal the previous pass had wrapped, nesting var() calls.)
        $store = [];
        $mask = function (string $s) use (&$store): string {
            return preg_replace_callback('/var\([^()]*\)/', function ($x) use (&$store) {
                $tag = "\x01" . count($store) . "\x01";
                $store[$tag] = $x[0];
                return $tag;
            }, $s);
        };
        $masked = $mask($line);
        $pairs = [];
        foreach ($m as $mm) { $pairs[$mm[1] . '|' . strtolower($mm[2])] = $mm; }
        foreach ($pairs as $mm) {
            [$whole, $token, $hex] = $mm;
            $pat = '/(?<![\w(#])' . preg_quote($hex, '/') . '\b/i';
            $masked = $mask(preg_replace_callback($pat, function ($x) use ($token, &$edits) {
                $edits++;
                return 'var(' . $token . ', ' . $x[0] . ')';
            }, $masked));
        }
        $out = strtr($masked, $store);
        if ($out !== $line) { $lines[$i] = $out; $changed = true; }
    }
    if ($changed) { file_put_contents($f->getPathname(), implode("\n", $lines) . "\n"); $files++; }
}
printf("filled %d partially-tokenised literals across %d files\n", $edits, $files);

// The new references are only replayable (apply.php after unapply.php) once they
// are in token-map.json, so re-capture the map from the tree we just wrote.
if ($edits) {
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/extract-map.php'), $rc);
    if ($rc !== 0) {
        fwrite(STDERR, "extract-map.php failed (exit $rc): token-map.json is stale\n");
        exit(1);
    }
}
