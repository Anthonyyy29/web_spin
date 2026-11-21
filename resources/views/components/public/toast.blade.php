@if (session('success') || session('error'))
    <div class="spin-toast-stack">
        @if (session('success'))
            <div data-toast role="status" class="spin-toast spin-toast--success">
                <span class="spin-toast__icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                </span>
                <span class="spin-toast__text">{{ session('success') }}</span>
                <button type="button" data-toast-close aria-label="Tutup notifikasi" class="spin-toast__close">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div data-toast role="alert" class="spin-toast spin-toast--error">
                <span class="spin-toast__icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </span>
                <span class="spin-toast__text">{{ session('error') }}</span>
                <button type="button" data-toast-close aria-label="Tutup notifikasi" class="spin-toast__close">&times;</button>
            </div>
        @endif
    </div>
@endif
