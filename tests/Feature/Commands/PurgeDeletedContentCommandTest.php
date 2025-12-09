<?php

declare(strict_types=1);

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('no content to purge', function () {
    test('command runs successfully when nothing to delete', function () {
        $this->artisan('content:purge-deleted')
            ->expectsOutput('No content found to purge.')
            ->assertSuccessful();
    });

    test('command runs successfully when only active content exists', function () {
        Page::factory()->count(3)->create();
        Service::factory()->count(2)->create();
        BlogPost::factory()->count(2)->create();

        $this->artisan('content:purge-deleted')
            ->expectsOutput('No content found to purge.')
            ->assertSuccessful();
    });
});

describe('dry run mode', function () {
    test('previews content without actually deleting', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        // Create and soft delete content older than 30 days
        $page = Page::factory()->create(['name' => 'Old Page']);
        $service = Service::factory()->create(['name' => 'Old Service']);

        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();
        $service->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --dry-run')
            ->expectsOutput('DRY RUN MODE: No content was deleted.')
            ->assertSuccessful();

        // Verify content still exists in database
        expect(Page::withTrashed()->find($page->id))->not->toBeNull();
        expect(Service::withTrashed()->find($service->id))->not->toBeNull();

        expect(Page::onlyTrashed()->count())->toBe(1);
        expect(Service::onlyTrashed()->count())->toBe(1);
    });

    test('displays summary table in dry run mode', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();
        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();
        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --dry-run')
            ->expectsOutputToContain('Page')
            ->expectsOutput('DRY RUN MODE: No content was deleted.')
            ->assertSuccessful();
    });
});

describe('force mode', function () {
    test('skips confirmation prompt with force flag', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();
        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();
        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->doesntExpectOutput('Purge cancelled.')
            ->assertSuccessful();

        expect(Page::withTrashed()->find($page->id))->toBeNull();
    });

    test('without force flag expects confirmation', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();
        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();
        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted')
            ->expectsConfirmation('Permanently delete this content? This action cannot be undone.', 'no')
            ->expectsOutput('Purge cancelled.')
            ->assertSuccessful();

        expect(Page::withTrashed()->find($page->id))->not->toBeNull();
    });
});

describe('soft delete age check', function () {
    test('only deletes content older than recovery period', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $oldPage = Page::factory()->create(['name' => 'Old Page']);
        $recentPage = Page::factory()->create(['name' => 'Recent Page']);

        // Old page deleted 35 days ago (beyond 30-day recovery)
        Carbon::setTestNow('2024-12-10 12:00:00');
        $oldPage->delete();

        // Recent page deleted 20 days ago (within 30-day recovery)
        Carbon::setTestNow('2025-02-09 12:00:00');
        $recentPage->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        expect(Page::withTrashed()->find($oldPage->id))->toBeNull();
        expect(Page::withTrashed()->find($recentPage->id))->not->toBeNull();
    });

    test('respects exactly 30 days cutoff', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page31Days = Page::factory()->create(['name' => 'Page 31 Days']);
        $page30Days = Page::factory()->create(['name' => 'Page 30 Days']);
        $page29Days = Page::factory()->create(['name' => 'Page 29 Days']);

        // 31 days ago from March 1 = January 29 11:59 (should be purged)
        Carbon::setTestNow('2025-01-29 11:59:00');
        $page31Days->delete();

        // Exactly 30 days ago from March 1 = January 30 12:00 (should be purged - cutoff is <=)
        Carbon::setTestNow('2025-01-30 12:00:00');
        $page30Days->delete();

        // 29 days ago from March 1 = January 31 12:01 (should be preserved)
        Carbon::setTestNow('2025-01-31 12:01:00');
        $page29Days->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // 31 days and 30 days should be purged (deleted <= cutoff date)
        expect(Page::withTrashed()->find($page31Days->id))->toBeNull();
        expect(Page::withTrashed()->find($page30Days->id))->toBeNull();

        // 29 days should be preserved (deleted > cutoff date)
        expect(Page::withTrashed()->find($page29Days->id))->not->toBeNull();
    });
});

describe('recent soft deletes preserved', function () {
    test('content deleted within recovery period is not purged', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $pages = [];
        for ($i = 1; $i <= 5; $i++) {
            $page = Page::factory()->create(['name' => "Recent Page {$i}"]);
            Carbon::setTestNow(now()->subDays($i));
            $page->delete();
            $pages[] = $page;
        }

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // All pages deleted within last 5 days should still exist
        foreach ($pages as $page) {
            expect(Page::withTrashed()->find($page->id))->not->toBeNull();
        }
    });
});

describe('all content types purged', function () {
    test('purges all supported content model types', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create(['name' => 'Old Page']);
        $service = Service::factory()->create(['name' => 'Old Service']);
        $blogPost = BlogPost::factory()->create(['name' => 'Old Blog Post']);
        $faq = Faq::factory()->create(['question' => 'Old FAQ?']);
        $testimonial = Testimonial::factory()->create(['author_name' => 'Old Author']);

        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();
        $service->delete();
        $blogPost->delete();
        $faq->delete();
        $testimonial->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Purged 1 Page record(s)')
            ->expectsOutputToContain('Purged 1 Service record(s)')
            ->expectsOutputToContain('Purged 1 BlogPost record(s)')
            ->expectsOutputToContain('Purged 1 Faq record(s)')
            ->expectsOutputToContain('Purged 1 Testimonial record(s)')
            ->assertSuccessful();

        expect(Page::withTrashed()->find($page->id))->toBeNull();
        expect(Service::withTrashed()->find($service->id))->toBeNull();
        expect(BlogPost::withTrashed()->find($blogPost->id))->toBeNull();
        expect(Faq::withTrashed()->find($faq->id))->toBeNull();
        expect(Testimonial::withTrashed()->find($testimonial->id))->toBeNull();
    });

    test('handles different counts of each content type', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        // Create and soft delete old content
        Carbon::setTestNow('2024-12-15 12:00:00');
        Page::factory()->count(3)->create()->each->delete();
        Service::factory()->count(2)->create()->each->delete();
        BlogPost::factory()->count(1)->create()->each->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Purged 3 Page record(s)')
            ->expectsOutputToContain('Purged 2 Service record(s)')
            ->expectsOutputToContain('Purged 1 BlogPost record(s)')
            ->assertSuccessful();
    });
});

describe('relationship cascade', function () {
    test('purges relationships where deleted content is source', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();

        // Create relationships
        $service->attachRelated($faq1, ['order' => 0]);
        $service->attachRelated($faq2, ['order' => 1]);

        expect(DB::table('content_relations')->count())->toBe(2);

        // Soft delete the service
        Carbon::setTestNow('2024-12-15 12:00:00');
        $service->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Deleted 1 content items and 2 relationships.')
            ->assertSuccessful();

        // Service should be purged
        expect(Service::withTrashed()->find($service->id))->toBeNull();

        // Relationships should be deleted
        expect(DB::table('content_relations')->count())->toBe(0);

        // FAQs should still exist (not soft deleted)
        expect(Faq::find($faq1->id))->not->toBeNull();
        expect(Faq::find($faq2->id))->not->toBeNull();
    });

    test('purges relationships where deleted content is target', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();
        $faq = Faq::factory()->create();

        // Create relationships where FAQ is the target
        $service1->attachRelated($faq, ['order' => 0]);
        $service2->attachRelated($faq, ['order' => 0]);

        expect(DB::table('content_relations')->count())->toBe(2);

        // Soft delete the FAQ
        Carbon::setTestNow('2024-12-15 12:00:00');
        $faq->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // FAQ should be purged
        expect(Faq::withTrashed()->find($faq->id))->toBeNull();

        // Relationships should be deleted
        expect(DB::table('content_relations')->count())->toBe(0);

        // Services should still exist
        expect(Service::find($service1->id))->not->toBeNull();
        expect(Service::find($service2->id))->not->toBeNull();
    });

    test('purges relationships as both source and target', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        // Page links to Service
        $page->attachRelated($service, ['order' => 0]);

        // Service links to FAQ
        $service->attachRelated($faq, ['order' => 0]);

        // Page links to FAQ
        $page->attachRelated($faq, ['order' => 0]);

        expect(DB::table('content_relations')->count())->toBe(3);

        // Soft delete the service (which is both source and target)
        Carbon::setTestNow('2024-12-15 12:00:00');
        $service->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // Service should be purged
        expect(Service::withTrashed()->find($service->id))->toBeNull();

        // Two relationships involving service should be deleted (Page->Service, Service->FAQ)
        // One relationship should remain (Page->FAQ)
        expect(DB::table('content_relations')->count())->toBe(1);

        // Verify the remaining relationship is Page->FAQ
        $remaining = DB::table('content_relations')->first();
        expect($remaining->source_type)->toBe(Page::class);
        expect($remaining->source_id)->toBe($page->id);
        expect($remaining->target_type)->toBe(Faq::class);
        expect($remaining->target_id)->toBe($faq->id);
    });

    test('counts relationships correctly in output', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();
        $service = Service::factory()->create();
        $faqs = Faq::factory()->count(5)->create();

        // Page links to 5 FAQs
        foreach ($faqs as $index => $faq) {
            $page->attachRelated($faq, ['order' => $index]);
        }

        // Service links to same 5 FAQs
        foreach ($faqs as $index => $faq) {
            $service->attachRelated($faq, ['order' => $index]);
        }

        expect(DB::table('content_relations')->count())->toBe(10);

        // Soft delete the page
        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Deleted 1 content items and 5 relationships.')
            ->assertSuccessful();

        // 5 relationships should remain (Service->FAQs)
        expect(DB::table('content_relations')->count())->toBe(5);
    });
});

describe('restore before purge', function () {
    test('restored content is not purged', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create(['name' => 'Restored Page']);

        // Soft delete 35 days ago
        Carbon::setTestNow('2024-12-10 12:00:00');
        $page->delete();

        // Restore the page
        Carbon::setTestNow('2025-02-20 12:00:00');
        $page->restore();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutput('No content found to purge.')
            ->assertSuccessful();

        // Page should still exist
        expect(Page::find($page->id))->not->toBeNull();
        expect($page->fresh()->deleted_at)->toBeNull();
    });

    test('restored content with old deleted_at timestamp is not purged', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page1 = Page::factory()->create(['name' => 'Page 1']);
        $page2 = Page::factory()->create(['name' => 'Page 2']);

        // Both deleted 35 days ago
        Carbon::setTestNow('2024-12-10 12:00:00');
        $page1->delete();
        $page2->delete();

        // Restore page1
        Carbon::setTestNow('2025-02-25 12:00:00');
        $page1->restore();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // Page1 should exist (restored)
        expect(Page::find($page1->id))->not->toBeNull();

        // Page2 should be purged
        expect(Page::withTrashed()->find($page2->id))->toBeNull();
    });
});

describe('mixed age content', function () {
    test('purges only old soft deletes when mixed with recent ones', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        // Create 3 old pages (40 days ago)
        $oldPages = Page::factory()->count(3)->create();
        Carbon::setTestNow('2024-12-05 12:00:00');
        foreach ($oldPages as $page) {
            $page->delete();
        }

        Carbon::setTestNow('2025-03-01 12:00:00');

        // Create 3 recent pages (10 days ago)
        $recentPages = Page::factory()->count(3)->create();
        Carbon::setTestNow('2025-02-19 12:00:00');
        foreach ($recentPages as $page) {
            $page->delete();
        }

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Purged 3 Page record(s)')
            ->assertSuccessful();

        // Old pages should be purged
        foreach ($oldPages as $page) {
            expect(Page::withTrashed()->find($page->id))->toBeNull();
        }

        // Recent pages should still exist
        foreach ($recentPages as $page) {
            expect(Page::withTrashed()->find($page->id))->not->toBeNull();
        }
    });

    test('handles mixed content types with different ages', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        // Old content
        $oldPage = Page::factory()->create();
        $oldService = Service::factory()->create();

        Carbon::setTestNow('2024-12-10 12:00:00');
        $oldPage->delete();
        $oldService->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        // Recent content
        $recentPage = Page::factory()->create();
        $recentService = Service::factory()->create();

        Carbon::setTestNow('2025-02-20 12:00:00');
        $recentPage->delete();
        $recentService->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Purged 1 Page record(s)')
            ->expectsOutputToContain('Purged 1 Service record(s)')
            ->assertSuccessful();

        // Old content should be purged
        expect(Page::withTrashed()->find($oldPage->id))->toBeNull();
        expect(Service::withTrashed()->find($oldService->id))->toBeNull();

        // Recent content should still exist
        expect(Page::withTrashed()->find($recentPage->id))->not->toBeNull();
        expect(Service::withTrashed()->find($recentService->id))->not->toBeNull();
    });
});

describe('transaction behavior', function () {
    test('purge operation runs within a transaction', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();

        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        // Verify purge succeeds and data is committed
        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // Verify the content was actually removed (transaction committed)
        expect(Page::withTrashed()->find($page->id))->toBeNull();
    });
});

describe('config recovery days', function () {
    test('respects custom recovery_days config', function () {
        // Set custom recovery period to 60 days
        Config::set('content.recovery_days', 60);

        Carbon::setTestNow('2025-03-01 12:00:00');

        $page45Days = Page::factory()->create(['name' => 'Page 45 Days']);
        $page65Days = Page::factory()->create(['name' => 'Page 65 Days']);

        // 45 days ago from 2025-03-01 = 2025-01-15 (within 60-day recovery)
        Carbon::setTestNow('2025-01-15 12:00:00');
        $page45Days->delete();

        // 65 days ago from 2025-03-01 = 2024-12-26 (beyond 60-day recovery)
        Carbon::setTestNow('2024-12-26 12:00:00');
        $page65Days->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Recovery period: 60 days')
            ->assertSuccessful();

        // 45-day old page should be preserved
        expect(Page::withTrashed()->find($page45Days->id))->not->toBeNull();

        // 65-day old page should be purged
        expect(Page::withTrashed()->find($page65Days->id))->toBeNull();
    });

    test('uses default 30 days when config not set', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        // Create old content to verify 30-day default is used
        $page = Page::factory()->create();
        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        // With default 30 days, content from Dec 15 (46 days old) should be purged
        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        expect(Page::withTrashed()->find($page->id))->toBeNull();
    });

    test('works with very short recovery period', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        // Set recovery period to 1 day
        Config::set('content.recovery_days', 1);

        $page2Days = Page::factory()->create(['name' => 'Page 2 Days']);
        $pageToday = Page::factory()->create(['name' => 'Page Today']);

        // 2 days ago
        Carbon::setTestNow('2025-02-27 11:00:00');
        $page2Days->delete();

        // Today
        Carbon::setTestNow('2025-03-01 11:59:00');
        $pageToday->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Recovery period: 1 days')
            ->assertSuccessful();

        // 2-day old page should be purged
        expect(Page::withTrashed()->find($page2Days->id))->toBeNull();

        // Today's page should be preserved
        expect(Page::withTrashed()->find($pageToday->id))->not->toBeNull();
    });
});

describe('edge cases', function () {
    test('handles large number of soft deleted content', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        // Create 100 pages
        $pages = Page::factory()->count(100)->create();

        Carbon::setTestNow('2024-12-15 12:00:00');
        foreach ($pages as $page) {
            $page->delete();
        }

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Purged 100 Page record(s)')
            ->assertSuccessful();

        expect(Page::withTrashed()->count())->toBe(0);
    });

    test('handles content with complex relationships', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();
        $services = Service::factory()->count(3)->create();
        $faqs = Faq::factory()->count(5)->create();

        // Create complex relationship graph
        foreach ($services as $index => $service) {
            $page->attachRelated($service, ['order' => $index]);
        }

        foreach ($faqs as $index => $faq) {
            $page->attachRelated($faq, ['order' => $index]);
            $services[0]->attachRelated($faq, ['order' => $index]);
        }

        $totalRelations = DB::table('content_relations')->count();
        expect($totalRelations)->toBe(13); // 3 services + 5 FAQs from page, 5 FAQs from service[0]

        // Soft delete the page
        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // Page should be purged
        expect(Page::withTrashed()->find($page->id))->toBeNull();

        // Only relationships from service[0] to FAQs should remain (5 relationships)
        expect(DB::table('content_relations')->count())->toBe(5);
    });

    test('displays cutoff date in output', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted')
            ->expectsOutputToContain('Purging content soft-deleted before: 2025-01-30')
            ->expectsOutput('No content found to purge.')
            ->assertSuccessful();
    });

    test('handles content deleted exactly at cutoff time', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $cutoff = now()->subDays(30);
        $page = Page::factory()->create();

        Carbon::setTestNow($cutoff);
        $page->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->assertSuccessful();

        // Page deleted exactly at cutoff should be purged (<=)
        expect(Page::withTrashed()->find($page->id))->toBeNull();
    });

    test('handles empty relationships gracefully', function () {
        Carbon::setTestNow('2025-03-01 12:00:00');

        $page = Page::factory()->create();

        Carbon::setTestNow('2024-12-15 12:00:00');
        $page->delete();

        Carbon::setTestNow('2025-03-01 12:00:00');

        $this->artisan('content:purge-deleted --force')
            ->expectsOutputToContain('Deleted 1 content items and 0 relationships.')
            ->assertSuccessful();
    });
});
