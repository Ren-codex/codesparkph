<?php

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function makeBooking(array $overrides = []): Booking
{
    return Booking::create(array_merge([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
        'service' => 'Custom Web Application',
        'details' => 'We want an internal tool for our team.',
        'status' => Booking::STATUS_NEW,
    ], $overrides));
}

test('bookings can be listed with status counts', function () {
    makeBooking();
    makeBooking(['email' => 'maria@example.com', 'status' => 'completed']);

    $this->get(route('admin.bookings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Bookings')
            ->has('bookings.data', 2)
            ->where('counts.new', 1)
            ->where('counts.completed', 1)
        );
});

test('bookings can be filtered by status', function () {
    makeBooking();
    makeBooking(['email' => 'maria@example.com', 'status' => 'completed']);

    $this->get(route('admin.bookings.index', ['status' => 'completed']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('bookings.data', 1)
            ->where('bookings.data.0.email', 'maria@example.com')
        );
});

test('bookings can be searched by name or email', function () {
    makeBooking();
    makeBooking(['name' => 'Maria Santos', 'email' => 'maria@example.com']);

    $this->get(route('admin.bookings.index', ['search' => 'Maria']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('bookings.data', 1)
            ->where('bookings.data.0.name', 'Maria Santos')
        );
});

test('a consultation can be scheduled and annotated', function () {
    $booking = makeBooking();

    $this->put(route('admin.bookings.update', $booking), [
        'status' => 'scheduled',
        'scheduled_at' => '2026-10-01 14:30:00',
        'notes' => 'Discovery call booked over Messenger.',
    ])->assertRedirect();

    $booking->refresh();

    expect($booking->status)->toBe('scheduled')
        ->and($booking->notes)->toBe('Discovery call booked over Messenger.')
        ->and($booking->scheduled_at->format('Y-m-d H:i'))->toBe('2026-10-01 14:30');
});

test('an unknown status is rejected', function () {
    $booking = makeBooking();

    $this->put(route('admin.bookings.update', $booking), [
        'status' => 'ghosted',
    ])->assertSessionHasErrors('status');

    expect($booking->refresh()->status)->toBe(Booking::STATUS_NEW);
});

test('a booking can be deleted', function () {
    $booking = makeBooking();

    $this->delete(route('admin.bookings.destroy', $booking))->assertRedirect();

    expect(Booking::count())->toBe(0);
});
