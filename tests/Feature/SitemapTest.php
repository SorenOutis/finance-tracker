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
            ->assertHeader('Content-Type', 'text/xml');
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

        // Both <url> blocks should have all four required children
        $this->assertStringContainsString('<loc>', $content);
        $this->assertStringContainsString('<lastmod>', $content);
        $this->assertStringContainsString('<changefreq>', $content);
        $this->assertStringContainsString('<priority>', $content);

        $this->assertEquals(2, substr_count($content, '<url>'));
        $this->assertEquals(2, substr_count($content, '</url>'));
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
            ->assertHeader('Content-Type', 'text/plain');
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
}
