@php
    $colors = \App\Helpers\ColorHelper::$colors;
    $c = $colors[$color] ?? $colors['Gray'];
@endphp
<span class="category-pill"
      style="background:{{ $c['bg'] }};color:{{ $c['text'] }};border:0.5px solid {{ $c['border'] }};
             padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;display:inline-block;">
    {{ $name }}
</span>
