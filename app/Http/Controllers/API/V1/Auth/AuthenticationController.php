<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Contracts\Services\Auth\AuthServiceContract;
use App\DTOs\LoginDTO;
use App\DTOs\NewUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Auth\LoginRequest;
use App\Http\Requests\API\V1\Auth\RegisterRequest;
use App\Http\Requests\API\V1\Auth\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthenticationController extends Controller
{
    public function __construct(
        protected AuthServiceContract $authService
    ) {}

    /**
     * Step 1: Request account onboarding.
     * Validates inputs, creates an OTP, packages into DTO, and dispatches email.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = NewUserDTO::fromArray($request->validated());

        $this->authService->requestAccount($dto);

        return response()->json([
            'success' => true,
            'message' => 'Check your email for the Verfication'
        ], Response::HTTP_OK);
    }

    /**
     * Step 2: Verify the OTP and permanently create the account.
     * Generates and returns a Sanctum API token upon success.
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyAccount(
            $request->input('email'),
            $request->input('otp')
        );

        return response()->json([
            'user' => $result['user'],
            'token' => $result['token']
        ], 201);
    }

    /**
     * Step 3: Login the user and return a token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromArray($request->validated());

        $result = $this->authService->login($dto->email, $dto->password);

        return response()->json([
            'user' => $result['user'],
            'token' => $result['token']
        ], Response::HTTP_OK);
    }
}
