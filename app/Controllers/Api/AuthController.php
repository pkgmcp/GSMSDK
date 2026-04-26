<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use GSMSDK\Core\Application;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

/**
 * Authentication Controller
 *
 * Handles user authentication, registration, token management,
 * and OAuth operations.
 */
class AuthController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Login with credentials
     */
    public function login(Request $request): Response
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
                'details' => json_decode($e->getMessage(), true),
            ], 422);
        }

        if ($this->app->auth->attempt($data)) {
            $user = $this->app->auth->user();

            return Response::json([
                'status' => 'success',
                'token' => $this->app->auth->generateApiToken(),
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ],
            ]);
        }

        return Response::json([
            'error' => 'Invalid credentials',
            'message' => 'The provided email or password is incorrect.',
        ], 401);
    }

    /**
     * Register new user
     */
    public function register(Request $request): Response
    {
        try {
            $data = $request->validate([
                'name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
                'details' => json_decode($e->getMessage(), true),
            ], 422);
        }

        // In production: Create user
        // Example: $user = User::create([
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        // ]);

        return Response::json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'token' => $this->app->auth->generateApiToken(),
        ], 201);
    }

    /**
     * Logout
     */
    public function logout(Request $request): Response
    {
        $this->app->auth->logout();

        return Response::json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): Response
    {
        if (!$this->app->auth->check()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json([
            'status' => 'success',
            'token' => $this->app->auth->generateApiToken(),
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
    }

    /**
     * Verify email
     */
    public function verify(Request $request): Response
    {
        $token = $request->get('token');

        if (empty($token)) {
            return Response::json([
                'error' => 'Token required',
            ], 400);
        }

        // In production: Verify email token
        // Example: if (!EmailVerification::verify($token)) { ... }

        return Response::json([
            'status' => 'success',
            'message' => 'Email verified successfully',
        ]);
    }

    /**
     * Forgot password
     */
    public function forgot(Request $request): Response
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
            ], 422);
        }

        // In production: Send reset email
        // Example: PasswordReset::sendResetLink($data['email']);

        return Response::json([
            'status' => 'success',
            'message' => 'Password reset link sent to your email',
        ]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request): Response
    {
        try {
            $data = $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
            ], 422);
        }

        // In production: Reset password
        // Example: PasswordReset::reset($data);

        return Response::json([
            'status' => 'success',
            'message' => 'Password reset successfully',
        ]);
    }

    /**
     * Check authentication status
     */
    public function check(Request $request): Response
    {
        return Response::json([
            'authenticated' => $this->app->auth->check(),
            'user' => $this->app->auth->user(),
        ]);
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): Response
    {
        if (!$this->app->auth->check()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json([
            'user' => $this->app->auth->user(),
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request): Response
    {
        if (!$this->app->auth->check()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        try {
            $data = $request->validate([
                'name' => 'sometimes|min:2|max:50',
                'email' => 'sometimes|email',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
            ], 422);
        }

        // In production: Update user
        // Example: $user->update($data);

        return Response::json([
            'status' => 'success',
            'message' => 'Profile updated',
            'user' => $this->app->auth->user(),
        ]);
    }

    /**
     * Change password
     */
    public function password(Request $request): Response
    {
        if (!$this->app->auth->check()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        try {
            $data = $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:8|confirmed',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
            ], 422);
        }

        // In production: Verify current password and update
        // Example: if (!password_verify($data['current_password'], $user->password_hash)) { ... }

        return Response::json([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * OAuth redirect
     */
    public function redirect(Request $request, string $provider): Response
    {
        // In production: Redirect to OAuth provider
        return Response::json([
            'error' => 'Not implemented',
        ], 501);
    }

    /**
     * OAuth callback
     */
    public function callback(Request $request, string $provider): Response
    {
        // In production: Handle OAuth callback
        return Response::json([
            'error' => 'Not implemented',
        ], 501);
    }
}
