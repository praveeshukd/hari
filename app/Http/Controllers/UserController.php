<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        if ($request->user()->is($user) && $request->validated('role') !== User::ROLE_ADMIN) {
            return back()->with('error', 'You cannot remove your own admin role.');
        }

        $user->update(['role' => $request->validated('role')]);

        return back()->with('success', "Updated role for {$user->name}.");
    }
}
