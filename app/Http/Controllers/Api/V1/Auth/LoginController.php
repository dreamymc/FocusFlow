<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, \App\Actions\LoginAction $action): JsonResponse
    {
        $user = $action->execute(
            $request->input('email'),
            $request->input('password'),
            $request->ip()
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))->additional([
            'meta' => [
                'token' => $token,
            ],
        ])->response()->setStatusCode(200);
    }
}
