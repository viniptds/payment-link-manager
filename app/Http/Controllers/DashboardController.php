<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $payments = Payment::select(DB::raw('count(*) as count, sum(value) as sum'))->first();

        $paid = Payment::select(DB::raw('count(*) as count, sum(value) as sum'))->where('status', Payment::STATUS_PAID)->first();
        $expired = Payment::select(DB::raw('count(*) as count, sum(value) as sum'))->where('status', Payment::STATUS_EXPIRED)->first();
        $canceled = Payment::select(DB::raw('count(*) as count, sum(value) as sum'))->where('status', Payment::STATUS_CANCELLED)->first();
        $active = Payment::select(DB::raw('count(*) as count, sum(value) as sum'))->where('status', Payment::STATUS_ACTIVE)->first();
        $inactive = Payment::select(DB::raw('count(*) as count, sum(value) as sum'))->where('status', Payment::STATUS_INACTIVE)->first();


        $data = [
            'numbers' => [
                'payments' => $payments->count,
                'paid' => $paid->count,
                'expired' => $expired->count,
                'canceled' => $canceled->count,
                'active' => $active->count,
                'inactive' => $inactive->count,
            ],
            'payments' => $payments->sum,
            'values' => [
                'paid' => $paid->sum,
                'expired' => $expired->sum,
                'canceled' => $canceled->sum,
                'active' => $active->sum,
                'inactive' => $inactive->sum,
            ],
            'labels' => [
                'Pagos',
                'Expirados',
                'Cancelados',
                'Ativos',
                'Inativos',
            ]
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
