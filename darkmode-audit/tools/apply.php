<?php
/**
 * Replay token-map.json onto pristine core LESS, and regenerate the token block.
 *
 * Idempotent: lines already carrying a var(--wn-…) are left alone, so this can
 * be run twice, or after a partial revert, without damage.
 *
 * Values come from tokens-values.json so that editing a colour is a one-line
 * change there rather than an edit to 661 source sites.
 */
require __DIR__ . '/lab.php';
$root   = coreRoot();
$map    = json_decode(file_get_contents(__DIR__ . '/token-map.json'), true);
$values = json_decode(file_get_contents(__DIR__ . '/tokens-values.json'), true);

// ---- rewrite the source ------------------------------------------------
$byFile = [];
foreach ($map as $e) { $byFile[$e['file']][$e['line']][] = $e; }

$files = $repl = $skipped = 0;
foreach ($byFile as $rel => $byLine) {
    $path = $root . '/' . $rel;
    if (!is_file($path)) { fwrite(STDERR, "  missing: $rel\n"); continue; }
    $raw = file_get_contents($path);
    $nl  = str_ends_with($raw, "\n");
    $lines = explode("\n", $nl ? substr($raw, 0, -1) : $raw);
    $changed = false;
    foreach ($byLine as $ln => $entries) {
        $i = $ln - 1;
        if (!isset($lines[$i])) { continue; }
        // Skip per entry, not per line: a line can hold several literals and a
        // partial application must be able to finish. The same token/literal pair
        // can appear more than once on a line (gradient stops), so count how many
        // are already applied and wrap only the remainder.
        $groups = [];
        foreach ($entries as $e) { $groups[$e['token'] . '|' . strtolower($e['hex'])][] = $e; }
        foreach ($groups as $group) {
            $e = $group[0];
            $h = $e['hex'];
            $short = (strlen($h) === 7 && $h[1] === $h[2] && $h[3] === $h[4] && $h[5] === $h[6])
                ? '#' . $h[1] . $h[3] . $h[5] : null;
            $needles = array_filter([$h, $short]);
            $applied = 0;
            foreach ($needles as $needle) {
                $applied += preg_match_all('/var\(\s*' . preg_quote($e['token'], '/') . '\s*,\s*' . preg_quote($needle, '/') . '\s*\)/i', $lines[$i]);
            }
            $todo = count($group) - $applied;
            $skipped += min($applied, count($group));
            if ($todo <= 0) { continue; }

            // Mask existing var() calls so a literal already serving as a fallback is
            // never wrapped a second time.
            $store = [];
            $masked = preg_replace_callback('/var\([^()]*\)/', function ($m) use (&$store) {
                $tag = "\x01" . count($store) . "\x01";
                $store[$tag] = $m[0];
                return $tag;
            }, $lines[$i]);
            foreach ($needles as $needle) {
                if ($todo <= 0) { break; }
                $pat = '/(?<![\w(#])' . preg_quote($needle, '/') . '\b/i';
                // preserve the literal's original casing so unapply round-trips exactly
                $masked = preg_replace_callback(
                    $pat, fn ($m) => 'var(' . $e['token'] . ', ' . $m[0] . ')', $masked, $todo, $n
                );
                $todo -= $n;
                $repl += $n;
                if ($n) { $changed = true; }
            }
            $lines[$i] = strtr($masked, $store);
        }
    }
    if ($changed) { file_put_contents($path, implode("\n", $lines) . ($nl ? "\n" : '')); $files++; }
}

// ---- regenerate the token block ----------------------------------------
$out = [
    // The header deliberately does not name the generator: it lives in a separate
    // plugin repo, so a core contributor cannot act on a reference to it. This file
    // reads as hand-maintained, which is how anyone working in core will treat it.
    // No standalone `//` separators: stylelint's scss/comment-no-empty flags them.
    '// Winter core colour tokens.',
    '// Every core colour resolves through this block, so dark mode and theming can',
    '// redefine these rather than overriding each rule that uses them. Each entry is',
    '// annotated with the literal it replaced.',
    ':root {',
];
// Tokens whose value needs a feature guard are emitted in a separate @supports
// block. That is deliberate and load-bearing: if the guard fails, the custom
// property is simply NEVER DEFINED, so the `var(--token, #hex)` fallback at each
// use site applies. Defining it unguarded would be worse than useless -- a
// var() fallback does NOT rescue a DEFINED-but-invalid value, so the whole
// declaration would be dropped at computed-value time and the element would
// paint transparent. (Verified in-browser; the usual "static first, var()
// second" two-declaration trick does not help either, for the same reason.)
$guarded = [];
foreach ($values as $tok => $v) {
    if (isset($v['supports'])) { $guarded[$v['supports']][$tok] = $v; }
}

$section = null;
foreach ($values as $tok => $v) {
    if (isset($v['supports'])) { continue; }   // emitted under its guard below
    if (($v['section'] ?? null) !== $section) {
        $section = $v['section'] ?? null;
        $out[] = '';
        $out[] = '    /* ' . $section . ' */';
    }
    // A token may carry a raw CSS expression instead of a literal -- used where a
    // colour must be DERIVED at runtime rather than baked at compile time (e.g.
    // color-mix() on a brand custom property, so user branding propagates).
    $value = $v['raw'] ?? $v['hex'];
    $note = isset($v['was']) && strtolower($v['was']) !== strtolower($v['hex'] ?? '')
        ? '  // was ' . $v['was'] . (isset($v['note']) ? ' — ' . $v['note'] : '')
        : (isset($v['note']) ? '  // ' . $v['note'] : '');
    $out[] = sprintf('    %s: %s;%s', $tok, $value, $note);
}
$out[] = '}';

foreach ($guarded as $cond => $toks) {
    $out[] = '';
    $out[] = '// Derived at runtime. Guarded so that where the feature is missing the';
    $out[] = '// token stays undefined and each use site falls back to its literal.';
    $out[] = '@supports ' . $cond . ' {';
    $out[] = '    :root {';
    foreach ($toks as $tok => $v) {
        $note = isset($v['note']) ? '  // ' . $v['note'] : '';
        $out[] = sprintf('        %s: %s;%s', $tok, $v['raw'], $note);
    }
    $out[] = '    }';
    $out[] = '}';
}

file_put_contents($root . '/modules/system/assets/ui/less/tokens.less', implode("\n", $out) . "\n");

// ---- ensure the import ---------------------------------------------------
$storm = $root . '/modules/system/assets/ui/storm.less';
$s = file_get_contents($storm);
if (!str_contains($s, 'tokens.less')) {
    file_put_contents($storm, str_replace('@import "less/global.less";',
        "@import \"less/tokens.less\";\n@import \"less/global.less\";", $s));
}

printf("applied %d rewrites across %d files (%d already applied)\n", $repl, $files, $skipped);
printf("wrote tokens.less with %d tokens\n", count($values));
