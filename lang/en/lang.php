<?php

return [
    'plugin' => [
        'name' => 'Tailwind UI',
        'description' => 'Provides a TailwindUI-based skin for the Winter CMS backend.',
    ],

    'branding' => [
        'background_image' => [
            'label' => 'Background Image',
            'comment' => 'The background image used on the Split Layout for the login screen.',
        ],
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

    'preferences' => [
        'dark_mode' => 'Dark Mode',
        'dark_mode_options' => [
            'auto' => 'automatic from your system settings',
            'light' => 'light',
            'dark' => 'dark',
        ],
    ],

    'toggle_dark_mode' => 'Toggle dark mode',
];
