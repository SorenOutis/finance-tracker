<?php

namespace App\Services;

use Carbon\Carbon;

class SitemapService
{
    public function generateSitemapXml(): string
    {
        $appUrl = config('app.url');

        $pages = [
            [
                'loc' => rtrim($appUrl, '/').'/',
                'lastmod' => Carbon::now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => rtrim($appUrl, '/').'/pricing',
                'lastmod' => Carbon::now()->toDateString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($page['loc'])."</loc>\n";
            $xml .= '    <lastmod>'.$page['lastmod']."</lastmod>\n";
            $xml .= '    <changefreq>'.$page['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$page['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function generateRobotsTxt(): string
    {
        $appUrl = rtrim(config('app.url'), '/');

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "\n";
        $content .= "Sitemap: {$appUrl}/sitemap.xml\n";

        return $content;
    }
}
