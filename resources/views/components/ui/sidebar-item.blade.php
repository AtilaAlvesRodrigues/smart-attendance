@props([
    'href'   => '#',
    'label'  => '',
    'active' => false,
    'badge'  => null,
])

<a href="{{ $href }}"
   title="{{ $label }}"
   class="pal-sidebar-item{{ $active ? ' pal-sidebar-item--active' : '' }}">
    <span class="pal-sidebar-icon">{{ $slot }}</span>
    <span class="pal-sidebar-label">{{ $label }}</span>
    @if($badge !== null && $badge > 0)
        <span class="pal-sidebar-badge">{{ $badge }}</span>
    @endif
</a>
