<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            $route = $request->user()->isAdmin()
                ? route('admin.dashboard')
                : route('karyawan.dashboard');

            return redirect()->intended($route);
        }

        return view('auth.verify-email');
    }
}
