<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', 86400, function () {
            return app(SitemapService::class)->generateSitemapXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
