<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function index(): Response
    {
        return Inertia::render('Welcome', [
            'services' => StoreBookingRequest::SERVICES,
            'budgets' => StoreBookingRequest::BUDGETS,
            'projects' => Project::published()->ordered()->get(),
        ]);
    }
}
