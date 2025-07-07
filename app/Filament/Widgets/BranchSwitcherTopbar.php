<?php

namespace App\Filament\Widgets;

use App\Models\Cabang; // Pastikan path model ini benar
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class BranchSwitcherTopbar extends Widget
{
    protected static string $view = 'filament.widgets.branch-switcher-topbar';

    /**
     * Metode ini adalah satu-satunya yang diperlukan untuk mengontrol
     * visibilitas widget. Widget hanya akan tampil jika
     * metode ini mengembalikan `true`.
     */
    public static function isVisible(): bool
    {
        // Logika ini sudah benar:
        // Widget hanya terlihat jika user login dan memiliki role 'owner'.
        return Auth::user()?->hasRole('owner') ?? false;
    }

    /**
     * Metode ini diperlukan untuk mengirim data cabang
     * ke file view (blade) Anda.
     */
    protected function getViewData(): array
    {
        return [
            'branches' => Cabang::all(),
            'activeBranchId' => session('active_branch_id'),
        ];
    }
}