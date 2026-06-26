<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabBookingResource;
use App\Models\LabBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LabApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('view laboratory');

        $bookings = LabBooking::with(['patient:id,name,mr_number', 'doctor.user:id,name'])
            ->withCount('items')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date, fn ($q, $d) => $q->whereDate('booking_date', $d))
            ->when($request->search, fn ($q, $s) => $q->whereHas('patient', fn ($p) => $p->where('name', 'like', "%$s%")->orWhere('mr_number', 'like', "%$s%")))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return LabBookingResource::collection($bookings);
    }

    public function show(LabBooking $lab): LabBookingResource
    {
        $this->authorize('view laboratory');
        $lab->load(['patient', 'items.test', 'doctor.user:id,name']);

        return new LabBookingResource($lab);
    }
}
