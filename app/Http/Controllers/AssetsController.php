<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AssetsController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Assets');
    }
}
