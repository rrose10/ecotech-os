<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ForcePasswordController extends Controller
{
    public function show(Request $request)
    {
        if (!$request->user()->must_change_password) {
            return redirect('/dashboard');
        }

        return view('auth.force-password');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Choose a password different from your current one.',
            ]);
        }

        $user->forceFill([
            'password' => $request->password, // hashed by the model cast
            'must_change_password' => false,
        ])->save();

        ActivityLogger::log('Account', 'Updated', 'Changed own password at first login');

        return redirect('/dashboard')->with('success', 'Password updated.');
    }
}
