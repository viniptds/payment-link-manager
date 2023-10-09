<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $payments = Payment::select()->count();
        $paid = Payment::select()->where('status', Payment::STATUS_PAID)->count();
        $expired = Payment::select()->where('status', Payment::STATUS_EXPIRED)->count();
        $canceled = Payment::select()->where('status', Payment::STATUS_CANCELLED)->count();
        $active = Payment::select()->where('status', Payment::STATUS_ACTIVE)->count();
        $inactive = Payment::select()->where('status', Payment::STATUS_INACTIVE)->count();

        $data = [
            'payments' => $payments,
            'paid' => $paid,
            'expired' => $expired,
            'canceled' => $canceled,
            'active' => $active,
            'inactive' => $inactive,
        ];
        return view('dashboard')->with('data', $data);
    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
