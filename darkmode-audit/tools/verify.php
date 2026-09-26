<?php
/**
 * Prove the token layer changed nothing structurally.
 *
 * Collapse the tokens back out of the COMPILED css (strip :root, rewrite
 * var(--wn-x, #hex) -> #hex) and compare to a pristine baseline. A byte compare
 * is too strict: making two values textually identical lets cssnano merge rules
 * and repack shorthands, which changes layout but not meaning. So this compares
 * the flattened (selector, property) -> value map instead.
 *
 *   --capture   rebuild from pristine source and store the baselines
 *
 * Re-capture after anything that changes core source -- notably `composer
 * update`, which overwrites modules/ with newer code than git HEAD and will
 * otherwise make upstream edits look like token regressions.
 */
require __DIR__ . '/lab.php';
$root = coreRoot();
$dir  = __DIR__ . '/baseline';
$targets = [
    'storm.css'  => 'modules/system/assets/ui/storm.css',
    'winter.css' => 'modules/backend/assets/css/winter.css',
];

if (in_array('--capture', $argv, true)) {
    // Every step must succeed: a baseline copied after a failed build would be
    // stale CSS that silently validates whatever it is compared against.
    $env   = 'WINTER_ROOT=' . escapeshellarg($root) . ' ';
    $php   = escapeshellarg(PHP_BINARY) . ' ';
    $step  = function (string $label, string $cmd): bool {
        echo "{$label}…\n";
        passthru($cmd, $rc);
        if ($rc !== 0) { fwrite(STDERR, "$label failed (exit $rc)\n"); }
        return $rc === 0;
    };
    $reapply = fn () => $step('re-applying', $env . $php . escapeshellarg(__DIR__ . '/apply.php'))
        && $step('rebuilding', $env . escapeshellarg(__DIR__ . '/build.sh'));

    if (!$step('reverting to pristine', $env . $php . escapeshellarg(__DIR__ . '/unapply.php'))) { exit(1); }
    if (!$step('building pristine', $env . escapeshellarg(__DIR__ . '/build.sh'))) {
        // leave the tree applied, not half-reverted
        $reapply();
        fwrite(STDERR, "no baseline written\n");
        exit(1);
    }
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    foreach ($targets as $name => $rel) { copy($root . '/' . $rel, "$dir/$name"); }
    if (!$reapply()) { exit(1); }
    echo "baselines captured in tools/baseline/\n";
    exit(0);
}

$NAMED = ['silver'=>'#c0c0c0','white'=>'#ffffff','black'=>'#000000','gray'=>'#808080','grey'=>'#808080'];

function flatten(string $css, array $NAMED, bool $collapse): array {
    if ($collapse) {
        // ALL token blocks, not just the first: guarded tokens live in a second
        // :root inside an @supports, and its color-mix() arguments would
        // otherwise read as ":root paints these colours".
        $css = preg_replace('/:root\{[^}]*--wn-[^}]*\}/', '', $css);
        $css = preg_replace('/var\(\s*--wn-[a-z0-9-]+\s*,\s*([^)]+?)\s*\)/', '$1', $css);
    }
    $css = preg_replace('/\/\*.*?\*\//s', '', $css);
    $css = preg_replace_callback('/#([0-9a-fA-F]{3})\b(?![0-9a-fA-F])/', function ($m) {
        $h = strtolower($m[1]); return '#'.$h[0].$h[0].$h[1].$h[1].$h[2].$h[2];
    }, $css);
    $css = preg_replace_callback('/#([0-9a-fA-F]{6})\b/', fn ($m) => '#'.strtolower($m[1]), $css);
    foreach ($NAMED as $n => $h) { $css = preg_replace('/(?<![\w-])'.$n.'(?![\w-])/i', $h, $css); }

    $out = [];
    preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $m, PREG_SET_ORDER);
    foreach ($m as $rule) {
        foreach (array_map('trim', explode(',', trim($rule[1]))) as $sel) {
            if ($sel === '') { continue; }
            foreach (explode(';', $rule[2]) as $d) {
                if (!str_contains($d, ':')) { continue; }
                [$p, $v] = explode(':', $d, 2);
                $out[$sel . '{' . trim($p)] = trim($v);
            }
        }
    }
    return $out;
}

/**
 * The colours a selector paints, per role.
 *
 * cssnano repacks shorthands once a rewrite makes two values textually equal
 * (`border:1px solid` + `border-color:#ddd #ddd transparent` becomes
 * `border:1px solid #ddd` + `border-bottom:1px solid transparent`). The
 * declaration text differs; the painted result does not. Comparing the colours
 * a rule sets, rather than the text of each declaration, tells the two apart.
 *
 * Grouped by role (text, background, border/outline, shadow, other) rather than
 * merged per selector: swapping `color:#000;background:#fff` for the reverse
 * paints the same set of colours but is plainly a repaint. Repacking only ever
 * moves a colour between longhands of the same role, so it still compares equal.
 */
function colourRole(string $prop): string {
    $prop = strtolower(trim($prop));
    if (in_array($prop, ['color', 'caret-color', 'fill', 'stroke'], true)) { return 'text'; }
    if (str_starts_with($prop, 'background')) { return 'background'; }
    if (str_starts_with($prop, 'border') || str_starts_with($prop, 'outline')) { return 'border'; }
    if (str_ends_with($prop, 'shadow')) { return 'shadow'; }
    return 'other';
}

function coloursBySelector(array $flat): array {
    $out = [];
    foreach ($flat as $k => $v) {
        $at   = strrpos($k, '{');
        $sel  = substr($k, 0, $at) . ' <' . colourRole(substr($k, $at + 1)) . '>';
        if (preg_match_all('/#[0-9a-f]{6}\b|\b(?:transparent|currentcolor)\b/i', $v, $m)) {
            foreach ($m[0] as $c) { $out[$sel][] = strtolower($c); }
        }
    }
    // DISTINCT colours, not a multiset: repacking a 3-value border-color into an
    // explicit 4-value one repeats a colour without painting anything new.
    foreach ($out as $sel => $cs) { $out[$sel] = array_values(array_unique($cs)); sort($out[$sel]); }
    return $out;
}

$bad = 0;
foreach ($targets as $name => $rel) {
    if (!is_file("$dir/$name")) {
        fwrite(STDERR, "no baseline for $name — run: php tools/verify.php --capture\n");
        exit(2);
    }
    $cur  = flatten(file_get_contents($root . '/' . $rel), $NAMED, true);
    $base = flatten(file_get_contents("$dir/$name"), $NAMED, false);
    $missing = array_diff_key($base, $cur);
    $added   = array_diff_key($cur, $base);
    $changed = [];
    foreach ($base as $k => $v) { if (isset($cur[$k]) && $cur[$k] !== $v) { $changed[$k] = [$v, $cur[$k]]; } }

    // The verdict: does any selector paint a different set of colours?
    $cb = coloursBySelector($base);
    $cc = coloursBySelector($cur);
    $repainted = [];
    foreach ($cb as $sel => $cols) {
        $after = $cc[$sel] ?? [];
        if ($cols !== $after) { $repainted[$sel] = [$cols, $after]; }
    }
    foreach ($cc as $sel => $cols) {
        if (!isset($cb[$sel]) && $cols) { $repainted[$sel] = [[], $cols]; }
    }

    // Deliberate repaints (a design fix, not a plumbing regression) are listed in
    // expected-repaints.json so this stays a signal rather than permanent noise.
    $expectFile = __DIR__ . '/expected-repaints.json';
    $expect = is_file($expectFile) ? json_decode(file_get_contents($expectFile), true) : [];
    $expected = $expect[$name] ?? [];
    $unexpected = [];
    foreach ($repainted as $sel => $v) {
        $ok = false;
        foreach ($expected as $pat) { if (str_contains($sel, $pat)) { $ok = true; break; } }
        if (!$ok) { $unexpected[$sel] = $v; }
    }
    $accounted = count($repainted) - count($unexpected);
    $repainted = $unexpected;
    if ($accounted) { printf("%-11s %d repaint(s) matched expected-repaints.json\n", $name, $accounted); }

    printf("%-11s declarations base=%d cur=%d | text-diffs: missing=%d added=%d changed=%d | UNEXPECTED REPAINTS: %d\n",
        $name, count($base), count($cur), count($missing), count($added), count($changed), count($repainted));
    foreach (array_slice($repainted, 0, 8, true) as $sel => $v) {
        printf("    REPAINTED %s\n      was: %s\n      now: %s\n", substr($sel, 0, 90),
            implode(' ', $v[0]) ?: '(none)', implode(' ', $v[1]) ?: '(none)');
    }
    if (count($repainted)) { $bad++; }
}
echo $bad
    ? "\nUNEXPECTED REPAINTS — a selector paints different colours and is not in\nexpected-repaints.json. Investigate, or add it there if deliberate.\n"
    : "\nNo selector paints a different colour set. Text-level diffs, if any, are\n"
    . "cssnano repacking shorthands after a rewrite made two values textually equal.\n";
exit($bad ? 1 : 0);
