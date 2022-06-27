<?php

return [
    'plugin' => [
        'name' => 'Tailwind UI',
        'description' => 'Provides a TailwindUI-based skin for the Winter CMS backend.',
    ],

    'branding' => [
        'auth_layout' => [
            'label' => 'Authentication Layout',
            'simple' => 'Simple (Centred)',
            'split' => 'Left sidebar (Split)',
        ],
        'menu_location' => [
            'label' => 'Menu Location',
            'top' => 'Top',
            'side' => 'Side',
        ],
        'menu_icons' => [
            'label' => 'Icon Location',
            'tile' => 'Above',
            'inline' => 'Beside',
            'hidden' => 'Hidden (text only)',
            'only' => 'Only (no text)',
        ],
    ],
];
