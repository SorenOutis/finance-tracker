<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SettingsController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Settings');
    }
}
