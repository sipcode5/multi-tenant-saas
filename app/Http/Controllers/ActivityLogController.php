<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $org = Organization::current();

        $query = Activity::query()
            ->where('properties->organization_id', $org?->id)
            ->with('causer')
            ->latest();

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', \App\Models\User::class);
        }

        if ($request->filled('event')) {
            $query->where('description', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return Inertia::render('ActivityLog/Index', [
            'activities' => $query->paginate(20)->withQueryString(),
            'filters'    => $request->only(['user_id', 'event', 'date_from', 'date_to']),
            'users'      => $org?->users()->get(['users.id', 'users.name'])->toArray() ?? [],
        ]);
    }
}
