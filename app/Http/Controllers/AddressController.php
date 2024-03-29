<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index(): JsonResponse
    {
        $addresses = Auth::user()->contacts()->with('addresses')->get()->pluck('addresses')->flatten();
        return response()->json(AddressResource::collection($addresses));
    }

    public function store(AddressCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_api_id'] = Auth::id();

        $address = Address::create($data);
        return response()->json(new AddressResource($address), 201);
    }

    public function show(Address $address): JsonResponse
    {
        $this->authorize('view', $address);

        return response()->json(new AddressResource($address));
    }

    public function update(AddressUpdateRequest $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);
        
        $data = $request->validated();
        $address->update($data);

        return response()->json(new AddressResource($address));
    }

    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json(null, 204);
    }
}

