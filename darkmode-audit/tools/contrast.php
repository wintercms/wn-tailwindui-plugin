<?php
/**
 * WCAG 2.1 contrast for token pairs, before vs after.
 *
 * Only pairs that ACTUALLY co-occur on an element are checked -- found by
 * walking the compiled CSS for rules that set both a colour and a background.
 * Guessing pairs flags combinations nobody renders (inverse text on white) and
 * buries the real findings.
 *
 * "was" comes from each token's recorded original value, so this separates
 * "this token made contrast worse" from "this was already failing".
 */
require __DIR__ . '/lab.php';
$root = coreRoot();

$now = $was = [];
foreach (file($root . '/modules/system/assets/ui/less/tokens.less') as $l) {
    if (!preg_match('/(--wn-[a-z0-9-]+):\s*(#[0-9a-fA-F]{3,8})/', $l, $m)) { continue; }
    $now[$m[1]] = strtolower($m[2]);
    $was[$m[1]] = preg_match('/was (#[0-9a-fA-F]{3,8})/', $l, $w) ? strtolower($w[1]) : strtolower($m[2]);
}

$pairs = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/modules'));
foreach ($it as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) !== 'css') { continue; }
    $src = file_get_contents($f->getPathname());
    if (!str_contains($src, 'var(--wn-')) { continue; }
    preg_match_all('/([^{}]+)\{([^{}]*)\}/', $src, $m, PREG_SET_ORDER);
    foreach ($m as $r) {
        if (!preg_match('/(?:^|;)\s*color\s*:\s*var\((--wn-[a-z0-9-]+)/', $r[2], $fg)) { continue; }
        if (!preg_match('/background(?:-color)?\s*:\s*var\((--wn-[a-z0-9-]+)/', $r[2], $bg)) { continue; }
        if ($fg[1] === $bg[1]) { continue; }   // decorative block, not text on a ground
        $pairs[$fg[1] . '|' . $bg[1]][] = trim(explode(',', $r[1])[0]);
    }
}

$rows = [];
foreach ($pairs as $k => $sels) {
    [$fg, $bg] = explode('|', $k);
    if (!isset($now[$fg], $now[$bg])) { continue; }
    $rows[] = ['fg' => $fg, 'bg' => $bg, 'sel' => $sels[0], 'n' => count($sels),
               'now' => contrastRatio($now[$fg], $now[$bg]),
               'was' => contrastRatio($was[$fg], $was[$bg])];
}
usort($rows, fn ($a, $b) => ($a['now'] - $a['was']) <=> ($b['now'] - $b['was']));

$broke = array_filter($rows, fn ($r) => $r['was'] >= 4.5 && $r['now'] < 4.5);
$pre   = array_filter($rows, fn ($r) => $r['was'] < 4.5);
printf("real co-occurring text/background pairs : %d\n", count($rows));
printf("contrast regressed by the tokens        : %d\n", count(array_filter($rows, fn ($r) => $r['now'] < $r['was'] - 0.01)));
printf("NEWLY dropped below AA 4.5:1            : %d\n", count($broke));
printf("already failing before (pre-existing)   : %d\n\n", count($pre));

printf("  %-26s %-26s %6s %6s  %s\n", 'text', 'background', 'was', 'now', 'selector');
foreach ($rows as $r) {
    $flag = $r['was'] >= 4.5 && $r['now'] < 4.5 ? '  <== BROKE AA'
          : ($r['now'] < $r['was'] - 0.01 ? '  <- worse'
          : ($r['was'] < 4.5 ? '  (pre-existing fail)' : ''));
    printf("  %-26s %-26s %6.2f %6.2f  %s%s\n",
        $r['fg'], $r['bg'], $r['was'], $r['now'], substr($r['sel'], 0, 28), $flag);
}
exit(count($broke) ? 1 : 0);
