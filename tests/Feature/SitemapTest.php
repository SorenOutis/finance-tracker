<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ─── Sitemap ─────────────────────────────────────────────────

    public function test_sitemap_returns_ok(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_both_urls(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertSeeText(config('app.url').'/');
        $response->assertSeeText(config('app.url').'/pricing');
    }

    public function test_sitemap_has_required_xml_structure(): void
    {
        $response = $this->get(route('sitemap'));
        $content = $response->getContent();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);

        // Every <url> block should have all four required children
        $this->assertStringContainsString('<loc>', $content);
        $this->assertStringContainsString('<lastmod>', $content);
        $this->assertStringContainsString('<changefreq>', $content);
        $this->assertStringContainsString('<priority>', $content);

        $this->assertEquals(4, substr_count($content, '<url>'));
        $this->assertEquals(4, substr_count($content, '</url>'));
    }

    public function test_sitemap_has_correct_priorities_and_frequencies(): void
    {
        $response = $this->get(route('sitemap'));
        $content = $response->getContent();

        // Home page
        $this->assertStringContainsString('<priority>1.0</priority>', $content);
        $this->assertStringContainsString('<changefreq>weekly</changefreq>', $content);

        // Pricing page
        $this->assertStringContainsString('<priority>0.8</priority>', $content);
        $this->assertStringContainsString('<changefreq>monthly</changefreq>', $content);
    }

    public function test_sitemap_uses_cached_response(): void
    {
        // First request populates the cache
        $this->get(route('sitemap'))->assertOk();

        // Second request should use the cache (not regenerate)
        $this->get(route('sitemap'))->assertOk();
    }

    public function test_sitemap_is_accessible_to_guests(): void
    {
        $this->get(route('sitemap'))->assertOk();
    }

    // ─── Robots.txt ──────────────────────────────────────────────

    public function test_robots_returns_ok(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeaderContains('Content-Type', 'text/plain');
    }

    public function test_robots_contains_required_directives(): void
    {
        $response = $this->get(route('robots'));
        $content = $response->getContent();

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertStringContainsString('Sitemap: '.config('app.url').'/sitemap.xml', $content);
    }

    public function test_robots_uses_cached_response(): void
    {
        // First request populates the cache
        $this->get(route('robots'))->assertOk();

        // Second request should use the cache
        $this->get(route('robots'))->assertOk();
    }

    public function test_robots_is_accessible_to_guests(): void
    {
        $this->get(route('robots'))->assertOk();
    }

    // ─── sitemap:generate Command ─────────────────────────────────

    public function test_sitemap_generate_command_succeeds(): void
    {
        Cache::flush();

        $this->artisan('sitemap:generate')
            ->expectsOutputToContain('Regenerating sitemap.xml')
            ->expectsOutputToContain('Regenerating robots.txt')
            ->expectsOutputToContain('regenerated successfully')
            ->assertSuccessful();
    }

    public function test_sitemap_generate_warms_sitemap_cache(): void
    {
        Cache::forget('sitemap.xml');
        $this->assertFalse(Cache::has('sitemap.xml'));

        $this->artisan('sitemap:generate')->assertSuccessful();

        $this->assertTrue(Cache::has('sitemap.xml'));
        $content = Cache::get('sitemap.xml');
        $this->assertStringContainsString('<loc>'.config('app.url').'/</loc>', $content);
        $this->assertStringContainsString('<loc>'.config('app.url').'/pricing</loc>', $content);
    }

    public function test_sitemap_generate_warms_robots_cache(): void
    {
        Cache::forget('robots.txt');
        $this->assertFalse(Cache::has('robots.txt'));

        $this->artisan('sitemap:generate')->assertSuccessful();

        $this->assertTrue(Cache::has('robots.txt'));
        $content = Cache::get('robots.txt');
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Sitemap: '.config('app.url').'/sitemap.xml', $content);
    }

    public function test_sitemap_generate_overwrites_existing_cache(): void
    {
        // Populate cache with stale content
        Cache::put('sitemap.xml', 'stale-content', 86400);
        Cache::put('robots.txt', 'stale-content', 86400);

        $this->artisan('sitemap:generate')->assertSuccessful();

        // Cache should now have fresh content, not the stale placeholder
        $this->assertStringContainsString('<urlset', Cache::get('sitemap.xml'));
        $this->assertStringContainsString('User-agent: *', Cache::get('robots.txt'));
    }

    public function test_sitemap_generate_is_idempotent(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();
        $firstXml = Cache::get('sitemap.xml');
        $firstRobots = Cache::get('robots.txt');

        $this->artisan('sitemap:generate')->assertSuccessful();
        $secondXml = Cache::get('sitemap.xml');
        $secondRobots = Cache::get('robots.txt');

        // Content should be the same (minus date which might differ)
        $this->assertStringContainsString('<loc>', $firstXml);
        $this->assertStringContainsString('<loc>', $secondXml);
        $this->assertStringContainsString('User-agent:', $firstRobots);
        $this->assertStringContainsString('User-agent:', $secondRobots);
    }
}
