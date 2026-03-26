@props([
    'label'       => '',
    'open'        => false,
    'icon'        => null,
])

@once
<style>
.pal-sidebar-group-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.6rem 0.65rem;
    border-radius: 5px;
    color: rgba(255, 255, 255, 0.42);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: 'Inter', sans-serif;
    transition: color 0.15s ease, background 0.15s ease;
    gap: 0.5rem;
    text-align: left;
}

.pal-sidebar-group-trigger:hover {
    color: rgba(255, 255, 255, 0.7);
    background: rgba(255, 255, 255, 0.03);
}

.pal-sidebar-group-chevron {
    flex-shrink: 0;
    transition: transform 0.22s ease;
}

.pal-sidebar-group[data-open="true"] .pal-sidebar-group-chevron {
    transform: rotate(180deg);
}

.pal-sidebar-group-content {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.25s ease;
}

.pal-sidebar-group[data-open="true"] .pal-sidebar-group-content {
    max-height: 400px;
}

/* Light mode */
.light-mode .pal-sidebar-group-trigger {
    color: rgba(30, 41, 59, 0.45);
}

.light-mode .pal-sidebar-group-trigger:hover {
    color: rgba(30, 41, 59, 0.7);
    background: rgba(0, 0, 0, 0.03);
}

/* Mobile: hide labels, keep icon only */
@media (max-width: 767px) {
    .pal-sidebar-group-trigger {
        display: none;
    }
    .pal-sidebar-group-content {
        max-height: none;
        overflow: visible;
        display: contents;
    }
}
</style>
<script>
function palToggleSidebarGroup(btn) {
    var group = btn.closest('.pal-sidebar-group');
    group.dataset.open = group.dataset.open === 'true' ? 'false' : 'true';
}
</script>
@endonce

<div class="pal-sidebar-group" data-open="{{ $open ? 'true' : 'false' }}">
    <button type="button" class="pal-sidebar-group-trigger" onclick="palToggleSidebarGroup(this)">
        <span>{{ $label }}</span>
        <svg class="pal-sidebar-group-chevron" width="12" height="12" fill="none"
             stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div class="pal-sidebar-group-content">
        {{ $slot }}
    </div>
</div>
