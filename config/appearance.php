<?php

return [
    'defaults' => [
        'palette' => 'yelenscol',
        'font' => 'inter',
        'heading_font' => 'poppins',
        'font_size' => 'md',
        'gradient' => 'aurore',
        'animated' => true,
        'sidebar' => 'light',
    ],

    'palettes' => [
        'yelenscol' => ['label' => 'YELENSCOL (indigo & or)', 'scale' => [
            50 => '#eef2ff', 100 => '#e0e7ff', 200 => '#c7d2fe', 300 => '#a5b4fc', 400 => '#818cf8',
            500 => '#6366f1', 600 => '#4f46e5', 700 => '#4338ca', 800 => '#3730a3', 900 => '#312e81', 950 => '#1e1b4b',
        ]],
        'ocean' => ['label' => 'Océan', 'scale' => [
            50 => '#eff6ff', 100 => '#dbeafe', 200 => '#bfdbfe', 300 => '#93c5fd', 400 => '#60a5fa',
            500 => '#3b82f6', 600 => '#2563eb', 700 => '#1d4ed8', 800 => '#1e40af', 900 => '#1e3a8a', 950 => '#172554',
        ]],
        'emeraude' => ['label' => 'Émeraude', 'scale' => [
            50 => '#ecfdf5', 100 => '#d1fae5', 200 => '#a7f3d0', 300 => '#6ee7b7', 400 => '#34d399',
            500 => '#10b981', 600 => '#059669', 700 => '#047857', 800 => '#065f46', 900 => '#064e3b', 950 => '#022c22',
        ]],
        'violet' => ['label' => 'Violet', 'scale' => [
            50 => '#f5f3ff', 100 => '#ede9fe', 200 => '#ddd6fe', 300 => '#c4b5fd', 400 => '#a78bfa',
            500 => '#8b5cf6', 600 => '#7c3aed', 700 => '#6d28d9', 800 => '#5b21b6', 900 => '#4c1d95', 950 => '#2e1065',
        ]],
        'rubis' => ['label' => 'Rubis', 'scale' => [
            50 => '#fff1f2', 100 => '#ffe4e6', 200 => '#fecdd3', 300 => '#fda4af', 400 => '#fb7185',
            500 => '#f43f5e', 600 => '#e11d48', 700 => '#be123c', 800 => '#9f1239', 900 => '#881337', 950 => '#4c0519',
        ]],
    ],

    'fonts' => [
        'inter' => ['label' => 'Inter', 'stack' => "'Inter', ui-sans-serif, system-ui, sans-serif", 'family' => 'inter:400,500,600,700'],
        'poppins' => ['label' => 'Poppins', 'stack' => "'Poppins', ui-sans-serif, system-ui, sans-serif", 'family' => 'poppins:400,500,600,700,800'],
        'figtree' => ['label' => 'Figtree', 'stack' => "'Figtree', ui-sans-serif, system-ui, sans-serif", 'family' => 'figtree:400,500,600,700'],
        'montserrat' => ['label' => 'Montserrat', 'stack' => "'Montserrat', ui-sans-serif, system-ui, sans-serif", 'family' => 'montserrat:400,500,600,700,800'],
        'nunito' => ['label' => 'Nunito', 'stack' => "'Nunito', ui-sans-serif, system-ui, sans-serif", 'family' => 'nunito:400,500,600,700,800'],
        'merriweather' => ['label' => 'Merriweather (empattement)', 'stack' => "'Merriweather', Georgia, serif", 'family' => 'merriweather:400,700'],
    ],

    'font_sizes' => ['sm' => ['label' => 'Compacte', 'px' => 13.5], 'md' => ['label' => 'Standard', 'px' => 14.5], 'lg' => ['label' => 'Confortable', 'px' => 16]],

    'gradients' => [
        'none' => 'Aucun (fond uni)',
        'aurore' => 'Aurore (indigo & or)',
        'soleil' => 'Soleil levant',
        'brume' => 'Brume douce',
    ],

    'sidebars' => ['light' => 'Menu clair', 'dark' => 'Menu bleu nuit'],
];
