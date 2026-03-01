<?php

namespace App\Http\Resources\Challenge;

use App\Enums\InviteCodeScope;
use App\Models\Challenge\ChallengeInviteCode;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ChallengeInviteCodeResource extends Resource
{
    public int $id;

    public string $code;

    public string $label;

    public InviteCodeScope $scope;

    public bool $is_active;

    #[WithCast(DateTimeInterfaceCast::class)]
    public CarbonInterface $created_at;

    public Lazy|int|null $users_count;

    public static function fromModel(ChallengeInviteCode $inviteCode): self
    {
        return self::from([
            'id' => $inviteCode->id,
            'code' => $inviteCode->code,
            'label' => $inviteCode->label,
            'scope' => $inviteCode->scope,
            'is_active' => $inviteCode->is_active,
            'created_at' => $inviteCode->created_at,
            'users_count' => Lazy::create(fn () => $inviteCode->users_count),
        ]);
    }
}
