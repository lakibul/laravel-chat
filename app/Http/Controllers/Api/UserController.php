<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        $users = User::where('id', '!=', $currentUser->id)
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        $currentUser = $request->user();

        if ($currentUser->id == $id) {
            return response()->json(['error' => 'Cannot get your own profile this way'], 400);
        }

        $user = User::select('id', 'name', 'email', 'created_at')
            ->findOrFail($id);

        return response()->json($user);
    }
}
