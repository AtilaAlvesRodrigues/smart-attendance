@props([
    'href'   => '#',
    'label'  => '',
    'active' => false,
    'badge'  => null,
    'curto'  => null,
])

<a href="{{ $href }}"
   title="{{ $label }}"
   aria-label="{{ $label }}"
   class="pal-sidebar-item{{ $active ? ' pal-sidebar-item--active' : '' }}"@if($active) aria-current="page"@endif>
    <span class="pal-sidebar-icon">{{ $slot }}</span>
    <span class="pal-sidebar-label"><span class="pal-sidebar-label-longo">{{ $label }}</span><span class="pal-sidebar-label-curto" aria-hidden="true">{{ $curto ?? $label }}</span></span>
    @if($badge !== null && $badge > 0)
        <span class="pal-sidebar-badge">{{ $badge }}</span>
    @endif
</a>
