@php
    $drawer = $drawer ?? false;
    $items = [
        [
            'label' => 'Dashboard',
            'route' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'label' => 'Categories',
            'route' => route('admin.categories.index'),
            'active' => request()->routeIs('admin.categories.*'),
            'icon' => 'folder',
        ],
        [
            'label' => 'Danh mục báo giá',
            'route' => route('admin.pricing-categories.index'),
            'active' => request()->routeIs('admin.pricing-categories.*'),
            'icon' => 'price',
        ],
        [
            'label' => 'Posts',
            'route' => route('admin.posts.index'),
            'active' => request()->routeIs('admin.posts.*'),
            'icon' => 'document',
        ],
        [
            'label' => 'Users',
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
            'icon' => 'users',
        ],
        [
            'label' => 'Comments',
            'route' => route('admin.comments.index'),
            'active' => request()->routeIs('admin.comments.*'),
            'icon' => 'chat',
        ],
        [
            'label' => 'Contacts',
            'route' => route('admin.contacts.index'),
            'active' => request()->routeIs('admin.contacts.*'),
            'icon' => 'mail',
        ],
        [
            'label' => 'Nội dung website',
            'route' => route('admin.site-content.index'),
            'active' => request()->routeIs('admin.site-content.*'),
            'icon' => 'settings',
        ],
    ];
@endphp

<aside
    id="{{ $drawer ? 'admin-sidebar-drawer' : 'admin-sidebar-primary' }}"
    @unless ($drawer)
        x-bind:class="sidebarCollapsed ? 'w-20' : 'w-64'"
    @endunless
    class="{{ $drawer ? 'flex h-full w-72 max-w-[85vw] flex-col' : 'sticky top-0 hidden h-screen shrink-0 flex-col md:flex' }} overflow-hidden bg-slate-950 text-white shadow-2xl transition-[width] duration-300 ease-out"
>
    <div class="flex h-16 items-center justify-between border-b border-white/10 px-4">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold shadow-lg shadow-blue-950/30">
                DS
            </span>
            <span
                @unless ($drawer)
                    x-show="! sidebarCollapsed"
                    x-transition.opacity.duration.150ms
                @endunless
                class="truncate text-base font-bold tracking-normal"
            >
                CHỮ KÝ SỐ VIP
            </span>
        </a>

        @if ($drawer)
            <button
                type="button"
                class="ui-focus rounded-lg p-2 text-slate-300 transition hover:bg-white/10 hover:text-white"
                x-on:click="closeSidebar()"
                aria-label="Đóng menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        @else
            <button
                type="button"
                class="ui-focus hidden rounded-lg p-2 text-slate-300 transition hover:bg-white/10 hover:text-white md:inline-flex"
                x-on:click="toggleSidebarCollapse()"
                x-bind:aria-expanded="(! sidebarCollapsed).toString()"
                aria-controls="admin-sidebar-primary"
                aria-label="Thu gọn sidebar"
            >
                <svg class="h-5 w-5 transition" x-bind:class="sidebarCollapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                </svg>
            </button>
        @endif
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" aria-label="Menu quản trị">
        @foreach ($items as $item)
            <a
                href="{{ $item['route'] }}"
                @if ($drawer)
                    x-on:click="closeSidebar()"
                @endif
                class="{{ $item['active']
                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/25'
                    : 'text-slate-300 hover:bg-white/10 hover:text-white' }} ui-focus group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition duration-200 ease-out"
            >
                <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                    @switch($item['icon'])
                        @case('dashboard')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z" />
                            </svg>
                            @break
                        @case('folder')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
                            </svg>
                            @break
                        @case('price')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12V8H4v4m16 0v8H4v-8m16 0H4m4-4V4h8v4M8 16h3m3 0h2" />
                            </svg>
                            @break
                        @case('document')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h5M9 13h6M9 17h6" />
                            </svg>
                            @break
                        @case('users')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            @break
                        @case('chat')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                            </svg>
                            @break
                        @case('mail')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m22 6-10 7L2 6" />
                            </svg>
                            @break
                        @case('settings')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 12v3M5.64 5.64l2.12 2.12m8.48 8.48 2.12 2.12M3 12h3m12 0h3M5.64 18.36l2.12-2.12m8.48-8.48 2.12-2.12" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            @break
                    @endswitch
                </span>

                <span
                    @unless ($drawer)
                        x-show="! sidebarCollapsed"
                        x-transition.opacity.duration.150ms
                    @endunless
                    class="truncate"
                >
                    {{ $item['label'] }}
                </span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-3 text-xs text-slate-400">
        <div
            @unless ($drawer)
                x-show="! sidebarCollapsed"
                x-transition.opacity.duration.150ms
            @endunless
            class="rounded-lg bg-white/5 px-3 py-2"
        >
            Admin Control Panel
        </div>
    </div>
</aside>
