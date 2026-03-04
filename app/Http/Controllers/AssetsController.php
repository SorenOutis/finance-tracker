<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Liability;
use Inertia\Inertia;

class AssetsController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        
        $assets = Asset::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $liabilities = Liability::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalAssets = $assets->sum('amount');
        $totalLiabilities = $liabilities->sum('amount');
        $netWorth = $totalAssets - $totalLiabilities;
        
        return Inertia::render('Assets', [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'netWorth' => $netWorth,
        ]);
    }
}
