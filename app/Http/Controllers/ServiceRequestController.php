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

        $validated = $request->validate([
            'requester_name' => ['required', 'string', 'max:100'],
            'requester_email' => ['required', 'email', 'max:255'],
            'item_name' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', 'string'],
        ]);

        $serviceRequest = $request->user()->requests()->create($validated)->refresh();

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
            'status' => ['required', 'string', 'max:20'],
        ]);

        $serviceRequest->update(['status' => $validated['status']]);

        return response()->json($serviceRequest);
    }
}
