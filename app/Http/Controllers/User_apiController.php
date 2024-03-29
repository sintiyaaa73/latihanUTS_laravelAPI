<?php

namespace App\Http\Controllers;

use App\Http\Requests\User_apiLoginRequest;
use App\Http\Requests\User_apiRegisterRequest;
use App\Http\Requests\User_apiUpdateRequest;
use App\Http\Resources\User_apiResource;
use App\Models\User_Api;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(User_apiRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (User_Api::where('username', $data['username'])->count() == 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'username' => ['username already registered']
                ]
            ], 400));
        }

        $user = new User_Api($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new User_apiResource($user))->response()->setStatusCode(201);
    }

    public function login(User_apiLoginRequest $request): User_apiResource
    {
        $data = $request->validated();
        $user = User_Api::where('username', $data['username'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['username or password wrong']
                ]
            ], 401));
        }

        $user->token = Str::uuid()->toString();
        $user->save();

        return new User_apiResource($user);
    }

    public function get(Request $request): User_apiResource
    {
        $user = Auth::user();
        return new User_apiResource($user);
    }

    public function update(User_apiUpdateRequest $request): User_apiResource
    {
        $data = $request->validated();
        $user = Auth::user();

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return new User_apiResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->token = null;
        $user->save();

        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }
}
