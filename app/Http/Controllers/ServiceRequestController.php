<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceRequestController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        Gate::authorize('viewAny', ServiceRequest::class);

        $user = $request->user();
        $requests = ServiceRequest::query();

        if (! $user->isAdmin()) {
            $requests->where('user_id', $user->id);
        }

        $serviceRequests = $requests->latest()->paginate();

        if ($request->expectsJson()) {
            return response()->json($serviceRequests);
        }

        return view('requests.index', compact('serviceRequests'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        Gate::authorize('create', ServiceRequest::class);

        $user = $request->user();
        $validated = $request->validate([
            'user_id' => ['missing'],
            'status' => ['missing'],
            'is_admin' => ['missing'],
            'role' => ['missing'],
            'item_name' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', 'string', 'max:2000'],
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity'],
            'purpose' => $validated['purpose'],
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'requester_email' => $user->email,
            'status' => 'pending',
        ])->refresh();

        if ($request->expectsJson()) {
            return response()->json($serviceRequest, 201);
        }

        return redirect()->route('requests.show', $serviceRequest);
    }

    public function show(Request $request, ServiceRequest $serviceRequest): JsonResponse|View
    {
        Gate::authorize('view', $serviceRequest);

        if ($request->expectsJson()) {
            return response()->json($serviceRequest);
        }

        return view('requests.show', compact('serviceRequest'));
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest): JsonResponse|RedirectResponse
    {
        Gate::authorize('view', $serviceRequest);
        Gate::authorize('updateStatus', $serviceRequest);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
        ]);

        $serviceRequest->update(['status' => $validated['status']]);

        if ($request->expectsJson()) {
            return response()->json($serviceRequest);
        }

        return redirect()->route('requests.show', $serviceRequest);
    }
}
