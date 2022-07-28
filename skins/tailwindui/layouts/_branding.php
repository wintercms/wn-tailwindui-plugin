<?php

use Backend\Models\BrandSetting;
use Mexitek\PHPColors\Color;

$primary = BrandSetting::get('primary_color', BrandSetting::PRIMARY_COLOR);
$secondary = BrandSetting::get('secondary_color', BrandSetting::SECONDARY_COLOR);

$getVariations = function ($colorString) {
    return Cache::remember("backend.brand.color.variations.{$colorString}", now()->addMonths(1), function () use ($colorString) {
        $color = new Color($colorString);
        return [
            'dark' => $color->darken(0.20),
            'darker' => $color->darken(0.30),
            'darkest' => $color->darken(0.40),
            'light' => $color->lighten(0.25),
            'lighter' => $color->lighten(0.30),
            'lightest' => $color->lighten(0.35),
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