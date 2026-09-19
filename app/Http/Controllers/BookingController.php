<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    /**
     * Store a project booking request from the landing page.
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        Booking::create($request->validated());

        return back();
    }
}
