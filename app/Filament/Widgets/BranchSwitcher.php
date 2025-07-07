<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Cabang;

class BranchSwitcher extends Widget
{
    protected static string $view = 'filament.widgets.branch-switcher';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'branches' => Cabang::all(),
            'activeBranchId' => session('active_branch_id'),
        ];
    }
}
