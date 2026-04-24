<?php

namespace App\Helpers;

class ColorHelper
{
    public static array $colors = [
        'Blue'   => ['bg'=>'#E6F1FB','text'=>'#0C447C','border'=>'#85B7EB','swatch'=>'#378ADD'],
        'Pink'   => ['bg'=>'#FBEAF0','text'=>'#72243E','border'=>'#ED93B1','swatch'=>'#D4537E'],
        'Amber'  => ['bg'=>'#FAEEDA','text'=>'#633806','border'=>'#FAC775','swatch'=>'#EF9F27'],
        'Teal'   => ['bg'=>'#E1F5EE','text'=>'#085041','border'=>'#5DCAA5','swatch'=>'#1D9E75'],
        'Purple' => ['bg'=>'#EEEDFE','text'=>'#3C3489','border'=>'#AFA9EC','swatch'=>'#7F77DD'],
        'Coral'  => ['bg'=>'#FAECE7','text'=>'#712B13','border'=>'#F0997B','swatch'=>'#D85A30'],
        'Green'  => ['bg'=>'#EAF3DE','text'=>'#27500A','border'=>'#97C459','swatch'=>'#639922'],
        'Gray'   => ['bg'=>'#F1EFE8','text'=>'#444441','border'=>'#B4B2A9','swatch'=>'#888780'],
        'Red'    => ['bg'=>'#FCEBEB','text'=>'#791F1F','border'=>'#F09595','swatch'=>'#E24B4A'],
    ];

    public static function pill(string $color, string $name): string
    {
        $c = self::$colors[$color] ?? self::$colors['Gray'];
        return "<span class='category-pill' style='background:{$c['bg']};color:{$c['text']};border:0.5px solid {$c['border']};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;display:inline-block;'>{$name}</span>";
    }

    public static function swatch(string $color): string
    {
        $c = self::$colors[$color] ?? self::$colors['Gray'];
        return $c['swatch'];
    }
}
