<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use App\Models\Testimonial;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class TestimonialResource extends Resource
{
    public int $id;

    public ?int $user_id;

    public Lazy|string|null $name;

    public Lazy|string|null $job_title;

    public Lazy|string|null $organisation;

    public string $content;

    public Lazy|string|null $avatar_path;

    public ?string $avatar;

    public string $display_name;

    public ?string $display_job_title;

    public ?string $display_organisation;

    public bool $is_published;

    public int $display_order;

    public Lazy|UserResource|null $user;

    public static function fromModel(Testimonial $testimonial): self
    {
        return self::from([
            'id' => $testimonial->id,
            'user_id' => $testimonial->user_id,
            'name' => Lazy::create(fn () => $testimonial->name),
            'job_title' => Lazy::create(fn () => $testimonial->job_title),
            'organisation' => Lazy::create(fn () => $testimonial->organisation),
            'content' => $testimonial->content,
            'avatar_path' => Lazy::create(fn () => $testimonial->avatar_path),
            'avatar' => $testimonial->avatar,
            'display_name' => $testimonial->display_name,
            'display_job_title' => $testimonial->display_job_title,
            'display_organisation' => $testimonial->display_organisation,
            'is_published' => $testimonial->is_published,
            'display_order' => $testimonial->display_order,
            'user' => Lazy::whenLoaded('user', $testimonial, fn () => $testimonial->user !== null ? UserResource::from($testimonial->user) : null),
        ]);
    }
}
