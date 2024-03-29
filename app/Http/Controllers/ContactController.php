<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index(): JsonResponse
    {
        $contacts = Auth::user()->contacts()->get();
        return response()->json(ContactResource::collection($contacts));
    }

    public function store(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_api_id'] = Auth::id();

        $contact = Contact::create($data);
        return response()->json(new ContactResource($contact), 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $this->authorize('view', $contact);

        return response()->json(new ContactResource($contact));
    }

    public function update(ContactUpdateRequest $request, Contact $contact): JsonResponse
    {
        $this->authorize('update', $contact);
        
        $data = $request->validated();
        $contact->update($data);

        return response()->json(new ContactResource($contact));
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return response()->json(null, 204);
    }
}

