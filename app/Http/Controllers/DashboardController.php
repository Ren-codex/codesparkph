<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'bookings' => Booking::count(),
                'newBookings' => Booking::where('status', Booking::STATUS_NEW)->count(),
                'openBookings' => Booking::open()->count(),
                'projects' => Project::count(),
                'publishedProjects' => Project::published()->count(),
            ],
            'latestBookings' => Booking::latest()->take(5)->get(),
            'upcoming' => Booking::whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->take(5)
                ->get(),
        ]);
    }
}
