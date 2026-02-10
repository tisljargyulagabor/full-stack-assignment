<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AI\ChatController;
use App\Models\ChatMessage;
use Illuminate\Validation\Rules\Password;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// LOGIN - STEP 1
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::whereRaw('LOWER(email) = ?', [Str::lower($request->email)])->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        Log::warning("Failed login attempt: " . $request->email);
        return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
    }

    $user->tokens()->delete();

    if ($user->two_factor_enabled) {
        return response()->json([
            'mfa_required' => true,
            'user_id' => $user->id
        ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
})->middleware('throttle:10,1');

// MFA VERIFICATION DURING LOGIN
use Illuminate\Support\Facades\RateLimiter;

Route::post('/login/mfa', function (Request $request) {
    if (!$request->expectsJson()) {
        return response()->json(['message' => 'JSON request required'], 400);
    }

    try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|digits:6',
        ]);

        $user = User::findOrFail($request->user_id);

        // --- Rate Limiter kulcs létrehozása a felhasználó ID alapján ---
        $throttleKey = 'mfa-attempts:' . $user->id;

        // Ellenőrizzük, hogy túllépte-e az 5 próbálkozást
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => "Too many failed attempts. Redirecting to login...",
                'redirect' => '/login', // A frontend ebből tudni fogja, hogy váltania kell
                'retry_after' => $seconds
            ], 429);
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code, 2);

        if ($valid) {
            // Siker esetén töröljük a számlálót
            RateLimiter::clear($throttleKey);

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
        }

        // --- Hiba esetén növeljük a próbálkozások számát (10 perces büntetés) ---
        RateLimiter::hit($throttleKey, 600);
        $remaining = RateLimiter::remaining($throttleKey, 5);

        return response()->json([
            'message' => 'Wrong MFA Code',
        ], 422);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => 'Invalid data format', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        Log::error("MFA Error: " . $e->getMessage());
        return response()->json(['message' => 'Internal server error during MFA'], 500);
    }
})->middleware('throttle:60,1');


/*
|--------------------------------------------------------------------------
| Password Reset Routes
|--------------------------------------------------------------------------
*/

// 1️⃣ Send forgot password link
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = PasswordFacade::sendResetLink($request->only('email'));
    Log::info("Password reset link requested: {$request->email}, status: {$status}");

    return $status === PasswordFacade::RESET_LINK_SENT
        ? response()->json(['message' => 'Reset link has been sent.'])
        : response()->json(['message' => __($status)], 400);
})->middleware('throttle:3,1');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => [
            'required',
            'string',
            Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols(),
            'confirmed'
        ],
    ]);

    $status = PasswordFacade::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
            Log::info("Successful password reset: " . $user->email);
        }
    );

    if ($status !== PasswordFacade::PASSWORD_RESET) {
        Log::warning("Failed password reset: {$request->email}, status: {$status}");
    }

    return $status === PasswordFacade::PASSWORD_RESET
        ? response()->json(['message' => 'Password changed successfully!'])
        : response()->json(['message' => __($status)], 400);
});

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', function (Request $request) {
        if ($request->user()) {
            $request->user()->tokens()->delete();
            Log::info("User logged out and tokens revoked: " . $request->user()->email);
        }

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    });
    // PROFILE EDIT
    Route::put('/user/profile', function (Request $request) {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => [
                'required',
                'string',
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed'
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            Log::info("Profile updated with password: " . $user->email);
        } else {
            Log::info("Profile updated: " . $user->email);
        }
        $user->save();

        return response()->json(['message' => 'Profile updated', 'user' => $user]);
    });

    // MFA FUNCTIONS
    Route::post('/user/mfa/setup', function (Request $request) {
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $user = $request->user();

        $secret = $google2fa->generateSecretKey();

        $qrCode = $google2fa->getQRCodeUrl('EventApp', $user->email, $secret);

        Log::info('MFA setup initiated', [
            'user_id' => $user->id
        ]);

        return response()->json([
            'secret' => $secret,
            'qr_code' => $qrCode
        ]);
    });


    Route::post('/user/mfa/verify', function (Request $request) {
        $request->validate([
            'code' => 'required|digits:6',
            'secret' => 'required|string'
        ]);

        $google2fa = new Google2FA();
        $user = $request->user();

        if ($google2fa->verifyKey($request->secret, $request->code)) {

            $user->google2fa_secret = $request->secret;
            $user->save();

            Log::info('MFA activated', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'MFA active',
                'user' => $user->fresh()
            ]);
        }

        Log::warning('MFA verify invalid code', [
            'user_id' => $user->id,
            'ip' => $request->ip()
        ]);

        return response()->json(['message' => 'Incorrect code!'], 422);
    });


    Route::post('/user/mfa/disable', function (Request $request) {
        $user = $request->user();

        $user->google2fa_secret = null;
        $user->save();

        Log::info('MFA deactivated', [
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => 'MFA deactivated',
            'user' => $user->fresh()
        ]);
    });

    // EVENTS
    Route::get('/events', function () {
        Log::info("Fetching events");
        return Event::orderBy('event_date', 'asc')->get();
    });
    Route::get('/events/user/{user_id}', function ($user_id) {
        Log::info("Fetching events for user {$user_id}");
        return Event::where('event_user_id', $user_id)->orderBy('event_date', 'asc')->get();
    });

    Route::post('/events', function (Request $request) {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date|after:now',
            'event_description' => 'nullable|string',
        ]);

        $validated['event_user_id'] = $request->user()->id;

        $event = Event::create($validated);

        Log::info("New event saved: ID " . ($event->event_id ?? $event->id));

        return response()->json([
            'message' => 'Created',
            'event' => $event
        ], 201);
    });

    Route::put('/events/{event_id}', function (Request $request, $event_id) {
        $event = Event::findOrFail($event_id);

        if ($request->user()->id !== $event->event_user_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Permission denied!'], 403);
        }

        $rules = [
            'event_name' => 'required|string|max:255',
            'event_description' => 'nullable|string',
            'event_date' => 'required|date',
        ];

        if ($request->event_date !== $event->event_date) {
            $rules['event_date'] .= '|after:now';
        }

        $validated = $request->validate($rules);
        $event->update($validated);

        return response()->json(['message' => 'Updated', 'event' => $event->fresh()]);
    });

    Route::delete('/events/{event_id}', function (Request $request, $event_id) {
        $event = Event::findOrFail($event_id);
        if ($request->user()->id !== $event->event_user_id && $request->user()->role !== 'admin') {
            Log::warning("Unauthorized event deletion attempt: {$request->user()->email}");
            return response()->json(['message' => 'Permission denied!'], 403);
        }
        $event->delete();
        Log::info("Event deleted: {$event_id} - {$request->user()->email}");
        return response()->json(['message' => 'Deleted']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {

        Route::get('/users', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                Log::warning("Unauthorized admin users fetch attempt: {$request->user()->email}");
                return response()->json(['message' => 'Permission denied!'], 403);
            }
            Log::info("Admin users fetched by: {$request->user()->email}");
            return User::orderBy('id', 'desc')->get();
        });

        Route::post('/users', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                Log::warning("Unauthorized admin user creation attempt: {$request->user()->email}");
                return response()->json(['message' => 'Permission denied!'], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    Password::min(12)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                ],
                'role' => 'required|in:admin,user',
            ]);

            $user = User::create([...$validated, 'password' => Hash::make($validated['password'])]);
            Log::info("Admin created new user: {$user->email} - by: {$request->user()->email}");
            return response()->json(['message' => 'Save successful', 'user' => $user], 201);
        });

        Route::put('/users/{id}', function (Request $request, $id) {
            if ($request->user()->role !== 'admin') {
                Log::warning("Unauthorized admin user update attempt: {$request->user()->email}");
                return response()->json(['message' => 'Permission denied!'], 403);
            }

            $user = User::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'role' => 'required|in:admin,user',
            ]);

            $user->update($validated);
            Log::info("Admin updated user: {$user->email} - by: {$request->user()->email}");
            return response()->json(['message' => 'Updated', 'user' => $user->fresh()]);
        });

        Route::delete('/users/{id}', function (Request $request, $id) {
            if ($request->user()->role !== 'admin') {
                Log::warning("Unauthorized admin user deletion attempt: {$request->user()->email}");
                return response()->json(['message' => 'Permission denied!'], 403);
            }

            if ($request->user()->id == $id) {
                Log::warning("Admin attempted to delete themselves: {$request->user()->email}");
                return response()->json(['message' => 'You cannot delete yourself!'], 400);
            }

            User::findOrFail($id)->delete();
            Log::info("Admin user deleted: {$id} - by: {$request->user()->email}");
            return response()->json(['message' => 'Deleted']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | AI & Chat
    |--------------------------------------------------------------------------
    */

// --- USER CHAT ENDPOINTS (A ChatBot.vue használja) ---
    Route::post('/chat', [ChatController::class, '__invoke']);
    Route::get('/chat/history/{sessionId}', function ($sessionId) {
        return ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();
    });

// --- ADMIN CHAT ENDPOINTS (Csak bejelentkezett adminoknak) ---
    Route::prefix('admin')->group(function () {

        // 1. Az összes aktív/flagelt beszélgetés listázása az admin panelhez
        Route::get('/flagged-sessions', function () {
            return DB::table('chat_messages')
                ->leftJoin('users', 'chat_messages.chat_message_user_id', '=', 'users.id')
                ->select(
                    'chat_messages.session_id',
                    DB::raw('MAX(users.name) as user_name'),
                    // Itt 1-est adunk vissza, ha bármelyik üzenet adminra vár
                    DB::raw('MAX(CASE WHEN chat_messages.needs_admin = true THEN 1 ELSE 0 END) as needs_admin'),
                    DB::raw('MAX(CASE WHEN chat_messages.is_closed = true THEN 1 ELSE 0 END) as is_closed'),
                    DB::raw('MAX(chat_messages.created_at) as last_message')
                )
                ->groupBy('chat_messages.session_id')
                ->orderBy('last_message', 'desc')
                ->get();
        });

        // 2. Egy konkrét session története az adminnak
        Route::get('/chat-history/{sessionId}', function ($sessionId) {
            return ChatMessage::where('session_id', $sessionId)
                ->orderBy('created_at', 'asc')
                ->get();
        });

        // 3. Admin válasza a felhasználónak
        Route::post('/reply', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $sessionId = $request->input('session_id');
            $message = $request->input('message');

            $userId = ChatMessage::where('session_id', $sessionId)
                ->whereNotNull('chat_message_user_id')
                ->latest()
                ->value('chat_message_user_id');

            ChatMessage::create([
                'session_id' => $sessionId,
                'sender' => 'admin',
                'message' => $message,
                'needs_admin' => true,
                'is_closed' => false,
                'chat_message_user_id' => $userId
            ]);

            return response()->json(['status' => 'success']);
        });

        // 4. Session lezárása (Kijavított, egyetlen route)
        Route::post('/close-session', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $sessionId = $request->input('session_id');

            // Minden üzenetet lezártra állítunk és az admin igényt töröljük
            ChatMessage::where('session_id', $sessionId)
                ->update([
                    'needs_admin' => false,
                    'is_closed' => true
                ]);

            // Opcionális: Bot üzenet mentése, hogy a user lássa: vége
            ChatMessage::create([
                'session_id' => $sessionId,
                'sender' => 'bot',
                'message' => 'The conversation has been closed by an agent.',
                'needs_admin' => false,
                'is_closed' => true
            ]);

            return response()->json(['status' => 'success', 'message' => 'Session closed successfully']);
        });
    });
});
