<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    function index(Request $request) 
    {
        $users = [];
        if ($request->user()->is_admin) {
            $users = User::select()->orderByDesc('created_at')->get();
        }

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $message = 'Este operador não tem permissões para criar.';
        if ($request->user()->is_admin) {

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'is_admin' => ['required', 'in:0,1']
            ]);
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $request->is_admin == 1 ? true : false
            ]);
            
            return redirect('users');
        }
        
        return redirect('users')->with('message', $message);
    }

    function toggleAdmin(User $user, Request $request)
    {
        if ($user->id != $request->user()->id) {
            $user->is_admin = !$user->is_admin;
            $user->save();
        }
        return redirect('users');
    }
}
