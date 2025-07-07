<x-filament::widget>
    <x-filament::card>
        {{-- Menggunakan div sebagai container utama dengan Alpine.js --}}
        <div class="flex items-center gap-x-4" x-data>

            {{-- Label yang terhubung dengan select via 'for' dan 'id' --}}
            <label for="branch-switcher" class="text-sm font-medium text-gray-950 dark:text-white">
                Cabang Aktif:
            </label>

            <select
                id="branch-switcher"
                {{-- Aksi redirect menggunakan event listener dari Alpine.js --}}
                @change="window.location.href = $event.target.value"
                {{-- Class untuk styling yang konsisten dengan input Filament --}}
                class="filament-forms-input block w-60 rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700"
            >
                {{-- Opsi untuk menampilkan semua cabang --}}
                <option value="{{ route('switch-branch', 'all') }}" @if (empty($activeBranchId)) selected @endif>
                    Semua Cabang
                </option>

                {{-- Looping untuk setiap cabang yang tersedia --}}
                @foreach ($branches as $cabang)
                    <option value="{{ route('switch-branch', $cabang->id) }}" @if ($activeBranchId == $cabang->id) selected @endif>
                        {{ $cabang->nama }}
                    </option>
                @endforeach
            </select>

        </div>
    </x-filament::card>
</x-filament::widget>