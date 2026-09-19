<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Monitor and manage project requests from the landing page.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Booking::STATUSES)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $bookings = Booking::query()
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status)
            )
            ->when(
                $filters['search'] ?? null,
                fn ($query, string $search) => $query->where(
                    fn ($q) => $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('service', 'like', "%{$search}%")
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Bookings', [
            'bookings' => $bookings,
            'statuses' => Booking::STATUSES,
            'filters' => [
                'status' => $filters['status'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
            'counts' => Booking::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Request updated.')]);

        return back();
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Request deleted.')]);

        return back();
    }
}
