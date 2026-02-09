<?php

namespace App\Http\Middleware;

use App\Http\Resources\User\PrivateUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $parentShare = parent::share($request);

        return array_merge($parentShare, [
            'name' => config('app.name'),
            'appUrl' => config('app.url'),
            'defaultMetaDescription' => config('content.default_meta_description'),
            'auth' => [
                'user' => $user !== null ? PrivateUserResource::fromModel(user: $user) : null,
                'permissions' => $user !== null ? $this->getUserPermissions($user) : [],
            ],
            'flash' => [
                ...($request->session()->get('flash') ?? []),
            ],
            'legalPages' => collect(Config::get(key: 'content.legal', default: []))
                ->map(fn (array $page): array => [
                    'title' => $page['title'],
                    'route' => route('legal.show', $page['slug']),
                ])
                ->all(),
            'transformImages' => Config::get('services.image-transform.base_url') !== null,
        ]);
    }

    /**
     * Get the user's permissions for sharing with the frontend.
     *
     * @return array<int, string>
     */
    private function getUserPermissions(User $user): array
    {
        // Admins get all permissions (represented as wildcard)
        if ($user->is_admin === true) {
            return ['*'];
        }

        return $user->getAllPermissions()->pluck('name')->toArray();
    }
}
