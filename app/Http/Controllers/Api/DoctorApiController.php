<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $doctors = Doctor::with(['user:id,name,email,phone,avatar', 'department:id,name'])
            ->when($request->department_id, fn ($q, $id) => $q->where('department_id', $id))
            ->when($request->search, fn ($q, $s) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%$s%")))
            ->where('status', 'active')
            ->get();

        return DoctorResource::collection($doctors);
    }
}
