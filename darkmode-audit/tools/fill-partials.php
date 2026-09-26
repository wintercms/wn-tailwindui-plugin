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
        // mask the existing var() calls so their fallbacks are not re-matched
        $masked = $line; $store = [];
        foreach ($m as $k => $mm) {
            $tag = "\x01$k\x01";
            $store[$tag] = $mm[0];
            $masked = str_replace($mm[0], $tag, $masked);
        }
        foreach ($m as $mm) {
            [$whole, $token, $hex] = $mm;
            $pat = '/(?<![\w(#])' . preg_quote($hex, '/') . '\b/i';
            $masked = preg_replace_callback($pat, function ($x) use ($token, &$edits) {
                $edits++;
                return 'var(' . $token . ', ' . $x[0] . ')';
            }, $masked);
        }
        $out = strtr($masked, $store);
        if ($out !== $line) { $lines[$i] = $out; $changed = true; }
    }
    if ($changed) { file_put_contents($f->getPathname(), implode("\n", $lines) . "\n"); $files++; }
}
printf("filled %d partially-tokenised literals across %d files\n", $edits, $files);
