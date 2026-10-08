<?php

/**
 * Per-agency Color Themes (Mjolnir card "FINAS: SETTINGS — Add Color Theme settings").
 *
 * Each theme overrides the DaisyUI colour tokens on :root so the whole system
 * (every page under that agency) adopts the selected palette. The 'default'
 * theme performs no override — the agency keeps the built-in Landas gold.
 *
 * Keys are persisted in agencies.settings['color_theme'] — keep them stable.
 *
 *   colors  => the CSS token overrides actually applied (empty = pure default).
 *   preview => three representative hex swatches for the settings UI.
 */
return [
    'themes' => [
        'default' => [
            'label'   => 'Landas Gold (default)',
            'colors'  => [],
            'preview' => ['#c9a84c', '#5f9ea0', '#b5651d'],
        ],
        'ocean' => [
            'label'   => 'Ocean Blue',
            'colors'  => [
                'primary'   => '#2563eb',
                'secondary' => '#0ea5e9',
                'accent'    => '#14b8a6',
            ],
            'preview' => ['#2563eb', '#0ea5e9', '#14b8a6'],
        ],
        'emerald' => [
            'label'   => 'Emerald',
            'colors'  => [
                'primary'   => '#059669',
                'secondary' => '#0891b2',
                'accent'    => '#f59e0b',
            ],
            'preview' => ['#059669', '#0891b2', '#f59e0b'],
        ],
        'royal' => [
            'label'   => 'Royal Purple',
            'colors'  => [
                'primary'   => '#7c3aed',
                'secondary' => '#6366f1',
                'accent'    => '#ec4899',
            ],
            'preview' => ['#7c3aed', '#6366f1', '#ec4899'],
        ],
        'sunset' => [
            'label'   => 'Sunset Orange',
            'colors'  => [
                'primary'   => '#ea580c',
                'secondary' => '#db2777',
                'accent'    => '#f59e0b',
            ],
            'preview' => ['#ea580c', '#db2777', '#f59e0b'],
        ],
        'rose' => [
            'label'   => 'Rose',
            'colors'  => [
                'primary'   => '#e11d48',
                'secondary' => '#f43f5e',
                'accent'    => '#8b5cf6',
            ],
            'preview' => ['#e11d48', '#f43f5e', '#8b5cf6'],
        ],
        'slate' => [
            'label'   => 'Slate',
            'colors'  => [
                'primary'   => '#475569',
                'secondary' => '#0ea5e9',
                'accent'    => '#f59e0b',
            ],
            'preview' => ['#475569', '#0ea5e9', '#f59e0b'],
        ],
    ],
];
