<?php

use App\Models\Booking;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the landing page exposes the bookable services and published projects', function () {
    // Drop the projects seeded by the migration so this asserts on its own data.
    Project::query()->delete();

    Project::create([
        'name' => 'Live One',
        'type' => 'Business Website',
        'summary' => 'Shown on the landing page.',
        'url' => 'https://example.com',
        'position' => 1,
        'is_published' => true,
    ]);

    Project::create([
        'name' => 'Hidden One',
        'type' => 'Business Website',
        'summary' => 'Not shown.',
        'url' => 'https://hidden.example.com',
        'position' => 2,
        'is_published' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('services', 6)
            ->has('budgets', 5)
            ->has('projects', 1)
            ->where('projects.0.name', 'Live One')
            ->where('projects.0.domain', 'example.com')
        );
});

test('a visitor can submit a booking request', function () {
    $response = $this->post(route('bookings.store'), [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
        'phone' => '+63 900 000 0000',
        'service' => 'Reservation & Booking System',
        'budget' => 'Not sure yet',
        'details' => 'We need online reservations for our restaurant in Zamboanga City.',
    ]);

    $response->assertRedirect();

    $booking = Booking::where('email', 'juan@example.com')->first();

    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe(Booking::STATUS_NEW);
});

test('a booking request requires a known service', function () {
    $this->post(route('bookings.store'), [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
        'service' => 'Rocket Science',
        'details' => 'We need online reservations for our restaurant.',
    ])->assertSessionHasErrors('service');

    expect(Booking::count())->toBe(0);
});

test('guests cannot reach the admin screens', function () {
    $this->get(route('admin.projects.index'))->assertRedirect(route('login'));
    $this->get(route('admin.bookings.index'))->assertRedirect(route('login'));
});
