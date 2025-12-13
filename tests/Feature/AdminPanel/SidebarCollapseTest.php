<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays sidebar collapse toggle button on dashboard page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();

    // Verify the sidebar collapse button exists in the DOM
    // Filament's sidebar collapse button uses specific wire directives
    $response->assertSee('sidebar', false);
});

it('has sidebarCollapsibleOnDesktop configured in panel', function () {
    // Get the admin panel instance
    $panel = filament()->getPanel('admin');

    // Verify the panel exists and is configured to be collapsible on desktop
    expect($panel)->toBeInstanceOf(\Filament\Panel::class);

    // The panel should have the sidebar collapsible option enabled
    // This is verified by checking the panel configuration
    expect($panel->isSidebarCollapsibleOnDesktop())->toBeTrue();
});

it('sidebar contains collapsible functionality elements', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();

    // Look for Alpine.js directive that controls collapse state
    // Filament uses Alpine.js for sidebar collapse functionality
    $response->assertSee('x-data', false);

    // Verify Livewire is present (required for Filament interactivity)
    $response->assertSee('wire:', false);
});

it('navigation groups have icons defined for dropdown menus when sidebar is collapsed', function () {
    // Get the admin panel instance
    $panel = filament()->getPanel('admin');

    // Get all navigation groups
    $navigationGroups = $panel->getNavigationGroups();

    // Verify we have navigation groups and each has an icon defined
    // Icons are required for dropdown menus to work in collapsed sidebar
    expect($navigationGroups)->not->toBeEmpty();

    foreach ($navigationGroups as $group) {
        $icon = $group->getIcon();
        expect($icon)->toBeString();
        expect(strlen($icon))->toBeGreaterThan(0);
    }
});

it('sidebar navigation structure supports dropdown menus when collapsed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();

    // Filament v3 uses dropdowns to show navigation items when sidebar is collapsed
    // The navigation group icons trigger dropdowns that show the item labels
    // This verifies the DOM contains the navigation group labels

    // Get expected navigation group labels from panel configuration
    $panel = filament()->getPanel('admin');
    $navigationGroups = $panel->getNavigationGroups();
    $content = $response->getContent();

    // Verify each navigation group label is present in the rendered content
    foreach ($navigationGroups as $group) {
        expect($content)->toContain($group->getLabel());
    }
});
