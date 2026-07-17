<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Regenerate the sitemap.xml and robots.txt cached responses';

    public function handle(): int
    {
        $this->components->task('Regenerating sitemap.xml', function () {
            Cache::put('sitemap.xml', app(SitemapService::class)->generateSitemapXml(), 86400);

            return Cache::has('sitemap.xml');
        });

        $this->components->task('Regenerating robots.txt', function () {
            Cache::put('robots.txt', app(SitemapService::class)->generateRobotsTxt(), 86400);

            return Cache::has('robots.txt');
        });

        $this->components->info('Sitemap and robots.txt cache regenerated successfully.');

        return self::SUCCESS;
    }
}
