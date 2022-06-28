<?php

return [
    'plugin' => [
        'name' => 'Tailwind UI',
        'description' => 'Skin basé sur TailwindUI pour le backend de Winter CMS.',
    ],

    'branding' => [
        'auth_layout' => [
            'label' => "Disposition de l'authentification",
            'simple' => 'Simple (centrée)',
            'split' => 'Barre latérale gauche (fractionnée)',
        ],
        'menu_location' => [
            'label' => 'Emplacement du menu',
            'top' => 'Haut',
            'side' => 'Côté',
        ],
        'menu_icons' => [
            'label' => 'Emplacement des icônes',
            'tile' => 'Au dessus',
            'inline' => 'Sur le côte',
            'hidden' => 'Cachés (texte seulement)',
            'only' => 'Seuls (pas de texte)',
        ],
    ],
];
