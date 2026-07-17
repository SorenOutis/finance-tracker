<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $content = Cache::remember('robots.txt', 86400, function () {
            return app(SitemapService::class)->generateRobotsTxt();
        });

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
