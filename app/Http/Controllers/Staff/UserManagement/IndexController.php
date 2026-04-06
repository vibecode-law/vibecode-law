<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Http\Controllers\BaseController;
use App\Http\Resources\User\AdminUserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\Permission\Models\Role;

class IndexController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('staff-area/users/index', [
            'users' => $this->getUsers(request: $request),
            'roles' => $this->getRoles(),
            'filters' => [
                'search' => $request->string('search')->value(),
                'role' => $request->string('role')->value(),
                'blocked' => $request->filled('blocked') ? $request->boolean('blocked') : null,
            ],
        ]);
    }

    /**
     * @return PaginatedDataCollection<int, AdminUserResource>
     */
    private function getUsers(Request $request): PaginatedDataCollection
    {
        $query = User::query()
            ->withCount('showcases');

        $this->applySearchFilter(query: $query, request: $request);
        $this->applyRoleFilter(query: $query, request: $request);
        $this->applyBlockedFilter(query: $query, request: $request);

        $users = $query
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: 25)
            ->withQueryString();

        return AdminUserResource::collect($users, PaginatedDataCollection::class)
            ->only(
                'id',
                'first_name',
                'last_name',
                'handle',
                'avatar',
                'email',
                'organisation',
                'is_admin',
                'roles',
                'blocked_from_submissions_at',
                'showcases_count',
                'created_at',
            );
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applySearchFilter(Builder $query, Request $request): void
    {
        if ($request->filled('search') === false) {
            return;
        }

        $search = $request->string('search')->value();
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('organisation', 'like', "%{$search}%");
        });
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applyRoleFilter(Builder $query, Request $request): void
    {
        if ($request->filled('role') === false) {
            return;
        }

        $query->role($request->string('role')->value());
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applyBlockedFilter(Builder $query, Request $request): void
    {
        if ($request->filled('blocked') === false) {
            return;
        }

        $blocked = $request->boolean('blocked');
        if ($blocked === true) {
            $query->whereNotNull('blocked_from_submissions_at');
        } else {
            $query->whereNull('blocked_from_submissions_at');
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function getRoles(): Collection
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name');
    }
}
