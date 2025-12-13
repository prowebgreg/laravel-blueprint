<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays all navigation groups with correct labels and icons', function () {
    $panel = filament()->getPanel('admin');
    $navigationGroups = $panel->getNavigationGroups();

    expect($navigationGroups)->toHaveCount(5);

    $expectedGroups = [
        'Public Pages' => 'heroicon-o-document-text',
        'Content Resources' => 'heroicon-o-rectangle-stack',
        'Media Library' => 'heroicon-o-photo',
        'SEO' => 'heroicon-o-globe-alt',
        'Settings' => 'heroicon-o-cog-6-tooth',
    ];

    foreach ($navigationGroups as $group) {
        $label = $group->getLabel();
        expect($expectedGroups)->toHaveKey($label);
        expect($group->getIcon())->toBe($expectedGroups[$label]);
    }
});

it('has all navigation groups collapsible', function () {
    $panel = filament()->getPanel('admin');
    $navigationGroups = $panel->getNavigationGroups();

    foreach ($navigationGroups as $group) {
        expect($group->isCollapsible())->toBeTrue();
    }
});

it('displays Public Pages navigation group with 4 items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)->toContain('Public Pages');
    expect($content)->toContain('All Public Pages');
    expect($content)->toContain('Static Pages');
    expect($content)->toContain('Services');
    expect($content)->toContain('Blog Posts');
});

it('displays Content Resources navigation group with 2 items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)->toContain('Content Resources');
    expect($content)->toContain('FAQs');
    expect($content)->toContain('Testimonials');
});

it('displays Media Library navigation group with 4 items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)->toContain('Media Library');
    expect($content)->toContain('Images');
    expect($content)->toContain('Videos');
    expect($content)->toContain('SVG');
    expect($content)->toContain('Brand Assets');
});

it('displays SEO navigation group with 4 items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)->toContain('SEO');
    expect($content)->toContain('Sitemap');
    expect($content)->toContain('Redirects');
    expect($content)->toContain('Structured Data');
    expect($content)->toContain('404 Pages');
});

it('displays Settings navigation group with 3 items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)->toContain('Settings');
    expect($content)->toContain('Website Details');
    expect($content)->toContain('Scripts');
    expect($content)->toContain('Users');
});

it('can access All Public Pages page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/all-public-pages-page');
    $response->assertSuccessful();
    $response->assertSee('All Public Pages');
});

it('can access Static Pages resource', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/pages');
    $response->assertSuccessful();
});

it('can access Services resource', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/services');
    $response->assertSuccessful();
});

it('can access Blog Posts resource', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/blog-posts');
    $response->assertSuccessful();
});

it('can access FAQs resource', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/faqs');
    $response->assertSuccessful();
});

it('can access Testimonials resource', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/testimonials');
    $response->assertSuccessful();
});

it('can access Images page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/images-page');
    $response->assertSuccessful();
    $response->assertSee('Images');
});

it('can access Videos page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/videos-page');
    $response->assertSuccessful();
    $response->assertSee('Videos');
});

it('can access SVG page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/svg-page');
    $response->assertSuccessful();
    $response->assertSee('SVG');
});

it('can access Brand Assets page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/brand-assets-page');
    $response->assertSuccessful();
    $response->assertSee('Brand Assets');
});

it('can access Sitemap page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/sitemap-page');
    $response->assertSuccessful();
    $response->assertSee('Sitemap');
});

it('can access Redirects resource', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/redirects');
    $response->assertSuccessful();
});

it('can access Structured Data page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/structured-data-page');
    $response->assertSuccessful();
    $response->assertSee('Structured Data');
});

it('can access 404 Pages page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/not-found-pages-page');
    $response->assertSuccessful();
    $response->assertSee('404 Pages');
});

it('can access Website Details page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/website-details-page');
    $response->assertSuccessful();
    $response->assertSee('Website Details');
});

it('can access Scripts & Integrations page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/scripts-integrations-page');
    $response->assertSuccessful();
    $response->assertSee('Scripts');
});

it('can access Users resource', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/users');
    $response->assertSuccessful();
});

it('verifies all navigation items are present in sidebar', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $content = $response->getContent();

    // Public Pages group (4 items)
    expect($content)->toContain('All Public Pages');
    expect($content)->toContain('Static Pages');
    expect($content)->toContain('Services');
    expect($content)->toContain('Blog Posts');

    // Content Resources group (2 items)
    expect($content)->toContain('FAQs');
    expect($content)->toContain('Testimonials');

    // Media Library group (4 items)
    expect($content)->toContain('Images');
    expect($content)->toContain('Videos');
    expect($content)->toContain('SVG');
    expect($content)->toContain('Brand Assets');

    // SEO group (4 items)
    expect($content)->toContain('Sitemap');
    expect($content)->toContain('Redirects');
    expect($content)->toContain('Structured Data');
    expect($content)->toContain('404 Pages');

    // Settings group (3 items)
    expect($content)->toContain('Website Details');
    expect($content)->toContain('Scripts');
    expect($content)->toContain('Users');

    // User name in user menu
    expect($content)->toContain($user->name);
});

it('verifies navigation groups appear in correct order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $content = $response->getContent();

    $publicPagesPos = strpos($content, 'Public Pages');
    $contentResourcesPos = strpos($content, 'Content Resources');
    $mediaLibraryPos = strpos($content, 'Media Library');
    $seoPos = strpos($content, 'SEO');
    $settingsPos = strpos($content, 'Settings');

    expect($publicPagesPos)->not->toBeFalse();
    expect($contentResourcesPos)->not->toBeFalse();
    expect($mediaLibraryPos)->not->toBeFalse();
    expect($seoPos)->not->toBeFalse();
    expect($settingsPos)->not->toBeFalse();
    expect($publicPagesPos)->toBeLessThan($contentResourcesPos);
    expect($contentResourcesPos)->toBeLessThan($mediaLibraryPos);
    expect($mediaLibraryPos)->toBeLessThan($seoPos);
    expect($seoPos)->toBeLessThan($settingsPos);
});

it('verifies unauthenticated users cannot access admin navigation', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/admin/login');
});

it('verifies authenticated users can access dashboard with navigation', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin');
    $response->assertSuccessful();
    $response->assertSee('Dashboard');
    $content = $response->getContent();
    expect($content)->toContain('Public Pages');
    expect($content)->toContain('Content Resources');
    expect($content)->toContain('Media Library');
    expect($content)->toContain('SEO');
    expect($content)->toContain('Settings');
});

it('displays breadcrumbs on resource listing pages', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Test Static Pages resource breadcrumb
    $response = $this->get('/admin/pages');
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Static Pages');

    // Test Services resource breadcrumb
    $response = $this->get('/admin/services');
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Services');

    // Test FAQs resource breadcrumb
    $response = $this->get('/admin/faqs');
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('FAQs');
});

it('displays breadcrumbs on custom pages', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Test Media Library pages
    $response = $this->get('/admin/images-page');
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Images');

    // Test SEO pages
    $response = $this->get('/admin/sitemap-page');
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Sitemap');

    // Test Settings pages
    $response = $this->get('/admin/website-details-page');
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Website Details');
});

it('displays breadcrumbs on resource edit pages', function () {
    $user = User::factory()->create();
    $page = \App\Models\Page::factory()->create();
    $service = \App\Models\Service::factory()->create();
    $faq = \App\Models\Faq::factory()->create();

    $this->actingAs($user);

    // Test Page edit breadcrumb shows resource name
    $response = $this->get("/admin/pages/{$page->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Static Pages');
    expect($content)->toContain('Edit');

    // Test Service edit breadcrumb
    $response = $this->get("/admin/services/{$service->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Services');
    expect($content)->toContain('Edit');

    // Test FAQ edit breadcrumb
    $response = $this->get("/admin/faqs/{$faq->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('FAQs');
    expect($content)->toContain('Edit');
});

it('complex resources display tabbed layout with sidebar on edit pages', function () {
    $user = User::factory()->create();
    $page = \App\Models\Page::factory()->create();
    $service = \App\Models\Service::factory()->create();
    $blogPost = \App\Models\BlogPost::factory()->create();

    $this->actingAs($user);

    // Test PageResource edit has tabs
    $response = $this->get("/admin/pages/{$page->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Page Content');
    expect($content)->toContain('SEO Data');
    expect($content)->toContain('URL Slug');

    // Test ServiceResource edit has tabs
    $response = $this->get("/admin/services/{$service->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Page Content');
    expect($content)->toContain('SEO Data');
    expect($content)->toContain('URL Slug');

    // Test BlogPostResource edit has tabs
    $response = $this->get("/admin/blog-posts/{$blogPost->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Page Content');
    expect($content)->toContain('SEO Data');
    expect($content)->toContain('URL Slug');
});

it('simple resources display single-section layout without tabs on edit pages', function () {
    $user = User::factory()->create();
    $faq = \App\Models\Faq::factory()->create();
    $testimonial = \App\Models\Testimonial::factory()->create();

    $this->actingAs($user);

    // Test FaqResource edit has single section (no tabs)
    $response = $this->get("/admin/faqs/{$faq->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('FAQ Details');
    expect($content)->not->toContain('SEO Data');

    // Test TestimonialResource edit has single section (no tabs)
    $response = $this->get("/admin/testimonials/{$testimonial->id}/edit");
    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('Testimonial Details');
    expect($content)->not->toContain('SEO Data');
});
