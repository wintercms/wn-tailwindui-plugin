<?php

use Backend\Models\BrandSetting;
use Mexitek\PHPColors\Color;

$primary = BrandSetting::get('primary_color', BrandSetting::PRIMARY_COLOR);
$secondary = BrandSetting::get('secondary_color', BrandSetting::SECONDARY_COLOR);

$getVariations = function ($colorString) {
    return Cache::remember("backend.brand.color.variations.{$colorString}", now()->addMonths(1), function () use ($colorString) {
        $color = new Color($colorString);
        $luminance = $color->getHsl()['L'] * 100;
        return [
            'dark' => $color->darken(($luminance / 4)),
            'darker' => $color->darken(($luminance / 4) * 2),
            'darkest' => $color->darken(($luminance / 4) * 3),
            'light' => $color->lighten((100 - $luminance) / 4),
            'lighter' => $color->lighten((100 - $luminance) / 4 * 2),
            'lightest' => $color->lighten((100 - $luminance) / 4 * 3),
        ];
    });
};

$colorVariations = [
    'primary' => [
        'value' => $primary,
        'variations' => $getVariations($primary),
    ],
    'secondary' => [
        'value' => $secondary,
        'variations' => $getVariations($secondary),
    ],
];
?>
<style>
    :root {
        <?php
            foreach ($colorVariations as $colorName => $config) {
                echo "--{$colorName}: {$config['value']};\n";
                foreach ($config['variations'] as $variation => $value) {
                    echo "--{$colorName}-{$variation}: #{$value};\n";
                }
            }
        ?>
    }
</style>