    {{-- Topbar --}}
    <header class="fixed top-0 left-0 right-0 z-30 h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4">
        <div class="flex items-center gap-3">
            {{-- Tombol toggle sidebar (mobile) --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ url('/dashboard') }}" class="text-xl font-bold text-indigo-600">
                Dashboard HR
            </a>
        </div>

        {{-- Profile (kanan) --}}
        <div class="relative">
            <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false"
                    class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-gray-100">
                <img class="w-9 h-9 rounded-full object-cover"
                     src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=6366f1&color=fff"
                     alt="Avatar">
                <div class="hidden sm:block text-left">
                    <p class="text-sm font-medium leading-tight">{{ Auth::user()->name ?? 'Nama User' }}</p>
                    <p class="text-xs text-gray-500 leading-tight">{{ Auth::user()->email ?? 'user@example.com' }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="profileOpen" x-transition x-cloak
                 class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1">
                <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Profil Saya</a>
                <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Pengaturan</a>
                <hr class="my-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>
