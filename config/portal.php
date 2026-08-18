<?php

return [
    /*
    | These values are rendered as CSS variables by the dashboard shell.
    | Administrator-level defaults and future user-level preferences can be
    | merged here without changing any dashboard component markup.
    */
    'themes' => [
        'administrator' => [
            'label' => 'Administration',
            'eyebrow' => 'Portal control center',
            'accent' => '#6657e8',
            'accent_rgb' => '102, 87, 232',
            'accent_dark' => '#4938c8',
            'sidebar' => '#17162b',
            'canvas' => '#f5f6fb',
            'font_size' => '15px',
            'radius' => '18px',
        ],
        'recruiter' => [
            'label' => 'Recruitment',
            'eyebrow' => 'Recruiter',
            'accent' => '#e86f3d',
            'accent_rgb' => '232, 111, 61',
            'accent_dark' => '#be4f24',
            'sidebar' => '#20243a',
            'canvas' => '#f8f6f3',
            'font_size' => '15px',
            'radius' => '20px',
        ],
        'talent' => [
            'label' => 'Career space',
            'eyebrow' => 'Dashboard',
            'accent' => '#0f9f8f',
            'accent_rgb' => '15, 159, 143',
            'accent_dark' => '#08796d',
            'sidebar' => '#122b35',
            'canvas' => '#f3f8f7',
            'font_size' => '15px',
            'radius' => '22px',
        ],
    ],
];
