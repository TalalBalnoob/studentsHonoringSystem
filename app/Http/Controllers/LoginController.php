<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller {
    /**
     * Handle the login request and issue a Sanctum access token.
     *
     * POST /api/login
     * Body: { "username": "admin", "password": "admin" }
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse {
        // ── 1. Validate incoming request ─────────────────────────────────────
        // Ensure both fields are present and are strings.
        // ValidationException is automatically caught by Laravel and returns a
        // 422 Unprocessable Entity response with the error messages.
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // ── 2. Look up the user by username ───────────────────────────────────
        // We query the `users` table by the `name` column.
        // If your users table stores usernames in a different column (e.g.
        // `username`), change 'name' to match that column.
        //
        // ⚠️  TESTING SHORTCUT — hardcoded credential check
        //     This block bypasses the database entirely so you can test the
        //     endpoint right away without seeding a real user.
        //     REMOVE THIS BLOCK (and the early return) before going to production.
        if ($request->username === 'admin' && $request->password === 'admin') {
            // Fetch or fake-find the first user in the DB to attach a token to.
            // If you have no users seeded yet, run: php artisan tinker
            //   then: User::factory()->create(['name' => 'admin'])
            $user = User::first();

            if (! $user) {
                // No users in the database at all — give a clear dev message.
                return response()->json([
                    'message' => 'No user found in the database. '
                        . 'Please seed a user first (see comment in LoginController).',
                ], 500);
            }

            // Issue the Sanctum personal access token.
            // 'auth_token' is just a human-readable name stored in the
            // personal_access_tokens table so you can identify it later.
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'      => 'Login successful (test mode)',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ]);
        }
        // ── END OF TESTING SHORTCUT ───────────────────────────────────────────

        // ── 3. Normal production flow ─────────────────────────────────────────

        // Find the user whose `name` matches the submitted username.
        $user = User::where('name', $request->username)->first();

        // ── 4. Verify the user exists and the password is correct ─────────────
        // Hash::check() safely compares the plain-text password against the
        // bcrypt hash stored in the database. Never compare plain text directly.
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Throw a ValidationException so Laravel returns a 422 with a
            // field-level error on `username`. You can change this to a plain
            // 401 if you prefer not to hint which field failed.
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // ── 5. (Optional) Revoke all previous tokens for this user ────────────
        // Uncomment the line below if you want single-session behaviour,
        // i.e. logging in from a new device invalidates all existing tokens.
        // $user->tokens()->delete();

        // ── 6. Issue a new Sanctum personal access token ─────────────────────
        // createToken() inserts a row into `personal_access_tokens` and returns
        // a NewAccessToken object. plainTextToken is the raw token string that
        // you send to the client — it is NOT stored in the DB (only its hash is),
        // so this is the only time you can read it.
        $token = $user->createToken('auth_token')->plainTextToken;

        // ── 7. Return the token to the client ────────────────────────────────
        // The client should store this token and send it on every subsequent
        // request as:  Authorization: Bearer <token>
        return response()->json([
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Log the user out by revoking their current token.
     *
     * POST /api/logout
     * Header: Authorization: Bearer <token>
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse {
        // currentAccessToken() returns the token model that was used to
        // authenticate this request. delete() removes it from the DB,
        // immediately invalidating it for all future requests.
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
