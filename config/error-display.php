<?php

return [
    'defaults' => [
        'placement' => 'right_popup', 'font_family' => 'system', 'font_size' => 14,
        'text_color' => '#7f1d1d', 'background_color' => '#fef2f2', 'accent_color' => '#dc2626',
        'density' => 'comfortable', 'motion' => 'slide', 'show_icon' => true,
        'allow_dismiss' => true, 'group_messages' => true, 'auto_dismiss_seconds' => 0,
    ],
    'placements' => [
        'top' => 'Top of screen', 'below_header' => 'Below header', 'above_footer' => 'Above footer',
        'dialog' => 'Dialogue box', 'right_popup' => 'Right popup', 'bottom_right' => 'Bottom-right toast',
    ],
    'fonts' => ['system' => 'System', 'serif' => 'Serif', 'mono' => 'Monospace', 'rounded' => 'Rounded'],
    'densities' => ['compact' => 'Compact', 'comfortable' => 'Comfortable', 'spacious' => 'Spacious'],
    'motions' => ['none' => 'None', 'fade' => 'Fade', 'slide' => 'Slide', 'bounce' => 'Gentle bounce'],
];
