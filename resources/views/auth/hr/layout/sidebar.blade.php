    {{-- Overlay (mobile) --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-16 left-0 bottom-0 z-20 w-64 bg-white border-r border-gray-200 transform transition-transform duration-200 lg:translate-x-0 overflow-y-auto">
        <nav class="p-4 space-y-1">
            @php
                $menus = [
                    ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
                    ['label' => 'Pengguna',  'url' => '/users',     'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4.13a4 4 0 100-8 4 4 0 000 8z'],
                    ['label' => 'Laporan',   'url' => '/reports',   'icon' => 'M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z'],
                    ['label' => 'Pengaturan','url' => '/settings',  'icon' => 'M10.3 4.3a1.7 1.7 0 013.4 0 1.7 1.7 0 002.5 1l.1-.1a1.7 1.7 0 012.4 2.4l-.1.1a1.7 1.7 0 001 2.5 1.7 1.7 0 010 3.4 1.7 1.7 0 00-1 2.5l.1.1a1.7 1.7 0 01-2.4 2.4l-.1-.1a1.7 1.7 0 00-2.5 1 1.7 1.7 0 01-3.4 0 1.7 1.7 0 00-2.5-1l-.1.1a1.7 1.7 0 01-2.4-2.4l.1-.1a1.7 1.7 0 00-1-2.5 1.7 1.7 0 010-3.4 1.7 1.7 0 001-2.5l-.1-.1a1.7 1.7 0 012.4-2.4l.1.1a1.7 1.7 0 002.5-1z'],
                ];
            @endphp

            @foreach ($menus as $menu)
                @php $active = request()->is(ltrim($menu['url'], '/') . '*'); @endphp
                <a href="{{ url($menu['url']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $active ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}"/>
                    </svg>
                    {{ $menu['label'] }}
                </a>
            @endforeach
        </nav>
    </aside>
