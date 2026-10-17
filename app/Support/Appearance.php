<?php

namespace App\Support;

use App\Models\School;

class Appearance
{
    public static function settings(?School $school): array
    {
        $defaults = config('appearance.defaults');
        $saved = is_array($school?->appearance) ? $school->appearance : [];
        $merged = array_merge($defaults, array_intersect_key($saved, $defaults));

        if (! isset(config('appearance.palettes')[$merged['palette']])) $merged['palette'] = $defaults['palette'];
        if (! isset(config('appearance.fonts')[$merged['font']])) $merged['font'] = $defaults['font'];
        if (! isset(config('appearance.fonts')[$merged['heading_font']])) $merged['heading_font'] = $defaults['heading_font'];
        if (! isset(config('appearance.font_sizes')[$merged['font_size']])) $merged['font_size'] = $defaults['font_size'];
        if (! isset(config('appearance.gradients')[$merged['gradient']])) $merged['gradient'] = $defaults['gradient'];
        if (! isset(config('appearance.sidebars')[$merged['sidebar']])) $merged['sidebar'] = $defaults['sidebar'];
        $merged['animated'] = (bool) $merged['animated'];

        return $merged;
    }

    public static function css(array $s): string
    {
        $vars = '';
        foreach (config('appearance.palettes')[$s['palette']]['scale'] as $shade => $hex) {
            $vars .= '--brand-'.$shade.':'.self::rgb($hex).';';
        }
        $fonts = config('appearance.fonts');
        $vars .= '--font-sans:'.$fonts[$s['font']]['stack'].';';
        $vars .= '--font-display:'.$fonts[$s['heading_font']]['stack'].';';

        return ':root{'.$vars.'}html{font-size:'.config('appearance.font_sizes')[$s['font_size']]['px'].'px}';
    }

    public static function fontsUrl(array $s, bool $all = false): string
    {
        $fonts = config('appearance.fonts');
        $keys = $all ? array_keys($fonts) : array_unique([$s['font'], $s['heading_font'], 'poppins']);

        return 'https://fonts.bunny.net/css?family='.implode('|', array_map(fn ($k) => $fonts[$k]['family'], $keys)).'&display=swap';
    }

    public static function bodyClass(array $s): string
    {
        return trim('ys-grad-'.$s['gradient'].($s['animated'] ? ' ys-animated' : '').($s['sidebar'] === 'dark' ? ' ys-side-dark' : ''));
    }

    private static function rgb(string $hex): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return "$r $g $b";
    }
}
