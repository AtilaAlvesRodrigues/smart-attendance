@props([
    'href'   => '#',
    'label'  => '',
    'active' => false,
])

<a href="{{ $href }}"
   title="{{ $label }}"
   class="pal-sidebar-item{{ $active ? ' pal-sidebar-item--active' : '' }}">
    <span class="pal-sidebar-icon">{{ $slot }}</span>
    <span class="pal-sidebar-label">{{ $label }}</span>
</a>
