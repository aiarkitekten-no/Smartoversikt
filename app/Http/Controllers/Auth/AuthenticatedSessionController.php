<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // LOG: Authentication attempt details
        \Log::info('Login attempt', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'x_requested_with' => $request->header('X-Requested-With'),
            'accept' => $request->header('Accept'),
        ]);
        
        try {
            $request->authenticate();
            
            \Log::info('Login successful', [
                'email' => $request->input('email'),
                'user_id' => auth()->id(),
            ]);
            
            $request->session()->regenerate();

            // ALWAYS return JSON for AJAX requests (check X-Requested-With header)
            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Authentication successful',
                    'redirect' => route('dashboard', absolute: false)
                ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Login failed - Invalid credentials', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
            ]);
            
            // CRITICAL FIX: Return JSON error for AJAX requests instead of throwing
            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'errors' => [
                        'email' => ['These credentials do not match our records.']
                    ]
                ], 422); // 422 Unprocessable Entity
            }
            
            // For non-AJAX, let Laravel handle it normally
            throw $e;
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
