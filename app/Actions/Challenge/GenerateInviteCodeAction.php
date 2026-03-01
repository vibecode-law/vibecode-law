<?php

namespace App\Actions\Challenge;

use App\Models\Challenge\ChallengeInviteCode;
use Illuminate\Support\Str;

class GenerateInviteCodeAction
{
    public function generate(): string
    {
        do {
            $code = Str::random(16);
        } while (ChallengeInviteCode::where('code', $code)->exists());

        return $code;
    }
}
