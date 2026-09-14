<?php
/**
 * CIELAB helpers. ΔE here is CIE76 — crude next to CIEDE2000, but the decisions
 * it backs (is this literal a member of that cluster, roughly how visible is a
 * shift) only ever needed an order of magnitude, and it is worth being able to
 * read the arithmetic.
 */
function hex2rgb(string $h): array {
    $h = ltrim(trim($h), '#');
    if (strlen($h) === 3) { $h = $h[0].$h[0].$h[1].$h[1].$h[2].$h[2]; }
    return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
}

function lab(array $rgb): array {
    $f = function ($c) { $c /= 255; return $c > 0.04045 ? pow(($c + 0.055) / 1.055, 2.4) : $c / 12.92; };
    [$r, $g, $b] = array_map($f, $rgb);
    $x = ($r * 0.4124 + $g * 0.3576 + $b * 0.1805) / 0.95047;
    $y = ($r * 0.2126 + $g * 0.7152 + $b * 0.0722);
    $z = ($r * 0.0193 + $g * 0.1192 + $b * 0.9505) / 1.08883;
    $g2 = fn ($t) => $t > 0.008856 ? pow($t, 1 / 3) : (7.787 * $t + 16 / 116);
    [$x, $y, $z] = [$g2($x), $g2($y), $g2($z)];
    return [116 * $y - 16, 500 * ($x - $y), 200 * ($y - $z)];
}

function deltaE(string $a, string $b): float {
    [$l1, $a1, $b1] = lab(hex2rgb($a));
    [$l2, $a2, $b2] = lab(hex2rgb($b));
    return sqrt(($l1 - $l2) ** 2 + ($a1 - $a2) ** 2 + ($b1 - $b2) ** 2);
}

/** Distance from the neutral axis. Near 0 = grey; a tint or a brand colour is not. */
function chroma(string $h): float {
    [, $a, $b] = lab(hex2rgb($h));
    return sqrt($a * $a + $b * $b);
}

/** WCAG 2.1 relative luminance + contrast ratio. */
function luminance(string $hex): float {
    $f = fn ($c) => ($c /= 255) <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
    [$r, $g, $b] = array_map($f, hex2rgb($hex));
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

function contrastRatio(string $a, string $b): float {
    $la = luminance($a); $lb = luminance($b);
    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
}

/**
 * Locate the Winter core root.
 *
 * NOT derived from __DIR__: this directory is normally reached through the
 * symlink plugins/winter/tailwindui -> plugins-src/Winter/TailwindUI, and
 * plugins-src is itself the same directory as the sibling Plugins/ checkout, so
 * realpath() walks out into an unrelated tree. Walk up from the working
 * directory looking for something only the core root has.
 */
function coreRoot(): string {
    $d = getcwd();
    for ($i = 0; $i < 8; $i++) {
        if (is_file($d . '/artisan') && is_file($d . '/modules/system/assets/ui/storm.less')) {
            return $d;
        }
        $up = dirname($d);
        if ($up === $d) { break; }
        $d = $up;
    }
    fwrite(STDERR, "Run these from the Winter core root (the directory containing artisan/ and modules/).\n");
    exit(1);
}
