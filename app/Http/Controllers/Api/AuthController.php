<?php

namespace App\Http\Controllers\Api;

use App\Domain\Common\Constants\AuthConstants;
use App\Domain\Common\Enums\ErrorMessage;
use App\Domain\Common\Enums\SuccessMessage;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken(AuthConstants::TOKEN_NAME)->plainTextToken;

            return $this->successResponse(
                [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
                SuccessMessage::REGISTRATION_SUCCESS->value,
                201
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'User registration');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse(ErrorMessage::UNAUTHORIZED->value, 401);
            }

            $token = $user->createToken(AuthConstants::TOKEN_NAME)->plainTextToken;

            return $this->successResponse(
                [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
                SuccessMessage::LOGIN_SUCCESS->value
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'User login');
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, SuccessMessage::LOGOUT_SUCCESS->value);
        } catch (Throwable $e) {
            return $this->handleException($e, 'User logout');
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(new UserResource($request->user()));
        } catch (Throwable $e) {
            return $this->handleException($e, 'Get user info');
        }
    }
}
