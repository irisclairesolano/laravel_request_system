<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ServiceRequest::class);

        $user = $request->user();
        $requests = ServiceRequest::query();

        if (! $user->isAdmin()) {
            $requests->where('user_id', $user->id);
        }

        return response()->json($requests->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
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
            ...$validated,
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'requester_email' => $user->email,
            'status' => 'pending',
        ])->refresh();

        return response()->json($serviceRequest, 201);
    }

    public function show(ServiceRequest $serviceRequest): JsonResponse
    {
        Gate::authorize('view', $serviceRequest);

        return response()->json($serviceRequest);
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        Gate::authorize('view', $serviceRequest);
        Gate::authorize('updateStatus', $serviceRequest);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
        ]);

        $serviceRequest->update(['status' => $validated['status']]);

        return response()->json($serviceRequest);
    }
}
