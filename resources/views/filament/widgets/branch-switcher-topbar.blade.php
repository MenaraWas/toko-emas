<div class="flex items-center space-x-3">
    <div class="flex items-center space-x-2 text-sm text-gray-600" style="margin-right: 10px">
        <i class="fas fa-building text-blue-500"></i>
        <span class="font-medium">Cabang Aktif :</span>
    </div>
    <div class="relative">
        <form>
            <select
                onchange="if(this.value){ window.location.href='{{ url('/switch-branch') }}/'+this.value }"
                class="appearance-none bg-white border border-gray-200 rounded-lg px-4 py-2 pr-8 text-sm font-medium text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 min-w-[160px]"
            >
                <option value="all" @if(session('active_branch_id') === null) selected @endif>
                    Semua Cabang
                </option>
                @foreach(\App\Models\Cabang::all() as $cabang)
                    <option value="{{ $cabang->id }}" @if(session('active_branch_id') == $cabang->id) selected @endif>
                        📍 {{ $cabang->nama }}
                    </option>
                @endforeach
            </select>
        </form>
        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none" style="margin-left: 10px">
            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
        </div>
    </div>
</div>