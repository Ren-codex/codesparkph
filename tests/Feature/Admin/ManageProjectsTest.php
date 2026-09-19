<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    // The create_projects migration seeds the projects the landing page
    // shipped with; clear them so each test controls its own fixtures.
    Project::query()->delete();
});

test('projects can be listed', function () {
    Project::create([
        'name' => 'Honda Cars Philippines',
        'type' => 'Business Website',
        'summary' => 'A dealership site.',
        'url' => 'https://www.hondaphil.com/',
        'position' => 1,
    ]);

    $this->get(route('admin.projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Projects')
            ->has('projects', 1)
            ->where('projects.0.domain', 'hondaphil.com')
        );
});

test('a project can be added', function () {
    $this->post(route('admin.projects.store'), [
        'name' => 'New Client',
        'type' => 'Landing Page',
        'summary' => 'A landing page for a local business.',
        'url' => 'https://newclient.test',
        'is_published' => true,
    ])->assertRedirect(route('admin.projects.index'));

    expect(Project::where('name', 'New Client')->exists())->toBeTrue();
});

test('a project url must be valid', function () {
    $this->post(route('admin.projects.store'), [
        'name' => 'Bad Client',
        'type' => 'Landing Page',
        'summary' => 'Broken link.',
        'url' => 'not-a-url',
    ])->assertSessionHasErrors('url');

    expect(Project::count())->toBe(0);
});

test('a project can be updated and hidden from the landing page', function () {
    $project = Project::create([
        'name' => 'Old Name',
        'type' => 'Landing Page',
        'summary' => 'Original summary.',
        'url' => 'https://example.com',
        'is_published' => true,
    ]);

    $this->post(route('admin.projects.update', $project), [
        'name' => 'New Name',
        'type' => 'Landing Page',
        'summary' => 'Updated summary.',
        'url' => 'https://example.com',
        'is_published' => false,
    ])->assertRedirect(route('admin.projects.index'));

    $project->refresh();

    expect($project->name)->toBe('New Name')
        ->and($project->is_published)->toBeFalse();
});

test('a cover image can be uploaded, replaced, and removed', function () {
    Storage::fake('public');

    $this->post(route('admin.projects.store'), [
        'name' => 'With Cover',
        'type' => 'Landing Page',
        'summary' => 'Has a screenshot.',
        'url' => 'https://example.com',
        'cover' => UploadedFile::fake()->image('shot.png', 1200, 675),
    ])->assertRedirect(route('admin.projects.index'));

    $project = Project::firstWhere('name', 'With Cover');
    $original = $project->cover_path;

    expect($original)->not->toBeNull();
    Storage::disk('public')->assertExists($original);
    expect($project->cover_url)->toContain($original);

    // Replacing the cover removes the file it supersedes.
    $this->post(route('admin.projects.update', $project), [
        'name' => 'With Cover',
        'type' => 'Landing Page',
        'summary' => 'Has a screenshot.',
        'url' => 'https://example.com',
        'cover' => UploadedFile::fake()->image('new-shot.png', 1200, 675),
    ])->assertRedirect(route('admin.projects.index'));

    $project->refresh();
    Storage::disk('public')->assertMissing($original);
    Storage::disk('public')->assertExists($project->cover_path);

    $replacement = $project->cover_path;

    $this->post(route('admin.projects.update', $project), [
        'name' => 'With Cover',
        'type' => 'Landing Page',
        'summary' => 'Has a screenshot.',
        'url' => 'https://example.com',
        'remove_cover' => true,
    ])->assertRedirect(route('admin.projects.index'));

    $project->refresh();

    expect($project->cover_path)->toBeNull()
        ->and($project->cover_url)->toBeNull();
    Storage::disk('public')->assertMissing($replacement);
});

test('a cover must be an image', function () {
    Storage::fake('public');

    $this->post(route('admin.projects.store'), [
        'name' => 'Bad Cover',
        'type' => 'Landing Page',
        'summary' => 'Wrong file type.',
        'url' => 'https://example.com',
        'cover' => UploadedFile::fake()->create('notes.pdf', 200, 'application/pdf'),
    ])->assertSessionHasErrors('cover');

    expect(Project::count())->toBe(0);
});

test('deleting a project removes its cover file', function () {
    Storage::fake('public');

    $this->post(route('admin.projects.store'), [
        'name' => 'Temp',
        'type' => 'Landing Page',
        'summary' => 'Going away.',
        'url' => 'https://example.com',
        'cover' => UploadedFile::fake()->image('shot.png'),
    ]);

    $project = Project::firstWhere('name', 'Temp');
    $path = $project->cover_path;

    $this->delete(route('admin.projects.destroy', $project));

    Storage::disk('public')->assertMissing($path);
});

test('a project can be deleted', function () {
    $project = Project::create([
        'name' => 'Remove Me',
        'type' => 'Landing Page',
        'summary' => 'Going away.',
        'url' => 'https://example.com',
    ]);

    $this->delete(route('admin.projects.destroy', $project))
        ->assertRedirect(route('admin.projects.index'));

    expect(Project::count())->toBe(0);
});
