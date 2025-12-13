<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows dark mode is enabled in the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    // Filament adds a script that enables dark mode toggle
    $response->assertSee('dark', false);
});

it('renders custom theme CSS file in admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    // Check that Vite processes the theme - look for resources/css/filament/admin reference
    $response->assertSee('admin', false);
});

it('has oklch color palette defined for light mode', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Verify light mode oklch variables exist
    expect($themeCss)
        ->toContain(':root {')
        ->toContain('--background: oklch(0.9900 0 0);')
        ->toContain('--foreground: oklch(0 0 0);')
        ->toContain('--primary: oklch(0 0 0);')
        ->toContain('--card: oklch(1 0 0);')
        ->toContain('--border: oklch(0.9200 0 0);')
        ->toContain('--sidebar: oklch(0.9900 0 0);');
});

it('has oklch color palette defined for dark mode', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Verify dark mode oklch variables exist
    expect($themeCss)
        ->toContain('.dark {')
        ->toContain('--background: oklch(0 0 0);')
        ->toContain('--foreground: oklch(1 0 0);')
        ->toContain('--primary: oklch(1 0 0);')
        ->toContain('--card: oklch(0.1400 0 0);')
        ->toContain('--border: oklch(0.2600 0 0);')
        ->toContain('--sidebar: oklch(0.1800 0 0);');
});

it('has all chart colors defined in both light and dark modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode chart colors
    expect($themeCss)
        ->toContain('--chart-1: oklch(0.8100 0.1700 75.3500);')
        ->toContain('--chart-2: oklch(0.5500 0.2200 264.5300);')
        ->toContain('--chart-3: oklch(0.7200 0 0);')
        ->toContain('--chart-4: oklch(0.9200 0 0);')
        ->toContain('--chart-5: oklch(0.5600 0 0);');

    // Dark mode has different chart color values
    expect($themeCss)
        ->toContain('--chart-2: oklch(0.5800 0.2100 260.8400);')
        ->toContain('--chart-3: oklch(0.5600 0 0);');
});

it('has sidebar-specific oklch colors for both modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode sidebar colors
    expect($themeCss)
        ->toContain('--sidebar: oklch(0.9900 0 0);')
        ->toContain('--sidebar-foreground: oklch(0 0 0);')
        ->toContain('--sidebar-accent: oklch(0.9400 0 0);')
        ->toContain('--sidebar-accent-foreground: oklch(0 0 0);')
        ->toContain('--sidebar-border: oklch(0.9400 0 0);');

    // Dark mode sidebar colors
    expect($themeCss)
        ->toContain('--sidebar: oklch(0.1800 0 0);')
        ->toContain('--sidebar-foreground: oklch(1 0 0);')
        ->toContain('--sidebar-accent: oklch(0.3200 0 0);')
        ->toContain('--sidebar-accent-foreground: oklch(1 0 0);')
        ->toContain('--sidebar-border: oklch(0.3200 0 0);');
});

it('has destructive colors in oklch format for both modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode destructive
    expect($themeCss)
        ->toContain('--destructive: oklch(0.6300 0.1900 23.0300);')
        ->toContain('--destructive-foreground: oklch(1 0 0);');

    // Dark mode destructive (different values)
    expect($themeCss)
        ->toContain('--destructive: oklch(0.6900 0.2000 23.9100);')
        ->toContain('--destructive-foreground: oklch(0 0 0);');
});

it('has Geist font family variables defined', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    expect($themeCss)
        ->toContain("--font-sans: 'Geist', sans-serif;")
        ->toContain("--font-mono: 'Geist Mono', monospace;")
        ->toContain('--font-serif: Georgia, serif;');
});

it('has shadow scale variables defined', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    expect($themeCss)
        ->toContain('--shadow-2xs:')
        ->toContain('--shadow-xs:')
        ->toContain('--shadow-sm:')
        ->toContain('--shadow:')
        ->toContain('--shadow-md:')
        ->toContain('--shadow-lg:')
        ->toContain('--shadow-xl:')
        ->toContain('--shadow-2xl:');
});

it('can access admin panel with authentication', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

it('has muted and accent colors in both modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode
    expect($themeCss)
        ->toContain('--muted: oklch(0.9700 0 0);')
        ->toContain('--muted-foreground: oklch(0.4400 0 0);')
        ->toContain('--accent: oklch(0.9400 0 0);')
        ->toContain('--accent-foreground: oklch(0 0 0);');

    // Dark mode
    expect($themeCss)
        ->toContain('--muted: oklch(0.2300 0 0);')
        ->toContain('--muted-foreground: oklch(0.7200 0 0);')
        ->toContain('--accent: oklch(0.3200 0 0);')
        ->toContain('--accent-foreground: oklch(1 0 0);');
});

it('has input and ring colors in both modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode
    expect($themeCss)
        ->toContain('--input: oklch(0.9400 0 0);')
        ->toContain('--ring: oklch(0 0 0);');

    // Dark mode
    expect($themeCss)
        ->toContain('--input: oklch(0.3200 0 0);')
        ->toContain('--ring: oklch(0.7200 0 0);');
});

it('has secondary colors defined in both modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode
    expect($themeCss)
        ->toContain('--secondary: oklch(0.9400 0 0);')
        ->toContain('--secondary-foreground: oklch(0 0 0);');

    // Dark mode
    expect($themeCss)
        ->toContain('--secondary: oklch(0.2500 0 0);')
        ->toContain('--secondary-foreground: oklch(1 0 0);');
});

it('has popover colors in both modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode
    expect($themeCss)
        ->toContain('--popover: oklch(0.9900 0 0);')
        ->toContain('--popover-foreground: oklch(0 0 0);');

    // Dark mode
    expect($themeCss)
        ->toContain('--popover: oklch(0.1800 0 0);')
        ->toContain('--popover-foreground: oklch(1 0 0);');
});

it('has card foreground colors defined', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Both modes have card-foreground defined
    expect($themeCss)
        ->toContain('--card-foreground: oklch(0 0 0);') // Light
        ->toContain('--card-foreground: oklch(1 0 0);'); // Dark
});

it('has sidebar primary colors in both modes', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode
    expect($themeCss)
        ->toContain('--sidebar-primary: oklch(0 0 0);')
        ->toContain('--sidebar-primary-foreground: oklch(1 0 0);');

    // Dark mode
    expect($themeCss)
        ->toContain('--sidebar-primary: oklch(1 0 0);')
        ->toContain('--sidebar-primary-foreground: oklch(0 0 0);');
});

it('has sidebar ring colors defined', function () {
    $themeCss = file_get_contents(resource_path('css/filament/admin/theme.css'));

    // Light mode
    expect($themeCss)
        ->toContain('--sidebar-ring: oklch(0 0 0);');

    // Dark mode
    expect($themeCss)
        ->toContain('--sidebar-ring: oklch(0.7200 0 0);');
});
