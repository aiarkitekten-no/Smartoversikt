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
        // LOG: Authentication attempt
        \Log::info('Login attempt', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
        ]);
        
        try {
            $request->authenticate();
            
            \Log::info('Login successful', [
                'email' => $request->input('email'),
                'user_id' => auth()->id(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Login failed - Invalid credentials', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
            ]);
            throw $e;
        }

        $request->session()->regenerate();

        // If this is an AJAX request, return JSON instead of redirect
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'redirect' => route('dashboard', absolute: false)
            ]);
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
