<?php
/**
 * Revert every rewrite: var(--wn-token, #hex) -> #hex.
 *
 * Lossless by construction, because the literal that was replaced is carried in
 * the fallback. That property is the whole safety story of this experiment: it
 * is why the change is revertible, why it can be re-applied freely, and why a
 * missing token block degrades to the original colours rather than to nothing.
 *
 *   --dry   report without writing
 */
require __DIR__ . '/lab.php';
$root = coreRoot();
$dry  = in_array('--dry', $argv, true);

$files = $restored = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/modules'));
foreach ($it as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) !== 'less') { continue; }
    $src = file_get_contents($f->getPathname());
    if (!str_contains($src, 'var(--wn-')) { continue; }
    // NOTE: token names contain digits (--wn-blockquote-level1-color). An
    // earlier [a-z-]+ here silently skipped those and broke the round-trip.
    $out = preg_replace('/var\(\s*--wn-[a-z0-9-]+\s*,\s*(#[0-9a-fA-F]{3,8})\s*\)/', '$1', $src, -1, $n);
    if (!$n) { continue; }
    $files++; $restored += $n;
    if (!$dry) { file_put_contents($f->getPathname(), $out); }
}

// The token block and its import are part of the change, so they go too.
$tokens = $root . '/modules/system/assets/ui/less/tokens.less';
$storm  = $root . '/modules/system/assets/ui/storm.less';
if (!$dry) {
    if (is_file($tokens)) { unlink($tokens); }
    $s = file_get_contents($storm);
    if (str_contains($s, 'tokens.less')) {
        file_put_contents($storm, preg_replace('/^@import "less\/tokens\.less";\R/m', '', $s));
    }
}

printf("%s: %d files, %d literals restored%s\n",
    $dry ? 'DRY RUN' : 'reverted', $files, $restored,
    $dry ? '' : "; removed tokens.less + its import");
