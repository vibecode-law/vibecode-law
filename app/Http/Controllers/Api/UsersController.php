<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = User::query()
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(50)->get()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => "{$user->first_name} {$user->last_name}",
            'email' => $user->email,
            'job_title' => $user->job_title,
            'organisation' => $user->organisation,
        ]);

        return response()->json($users);
    }
}
