<div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5" x-data>
    <div class="flex flex-wrap items-center gap-3">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
            Customer location
        </span>
        <button
            type="button"
            class="inline-flex items-center justify-center gap-x-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            @click="
                if (! navigator.geolocation) {
                    alert('Geolocation is not supported by this browser.');
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => $wire.call('setCapturedLocation', pos.coords.latitude, pos.coords.longitude),
                    () => alert('Could not read location. Allow location access and try again.'),
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            "
        >
            Use my current location
        </button>
        @if ($this->captured_latitude !== null && $this->captured_longitude !== null)
            <span class="text-xs text-success-600 dark:text-success-400">
                Captured: {{ number_format($this->captured_latitude, 5) }},
                {{ number_format($this->captured_longitude, 5) }}
            </span>
        @else
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Optional — saves visit GPS and updates the customer shop coordinates.
            </span>
        @endif
    </div>
</div>
