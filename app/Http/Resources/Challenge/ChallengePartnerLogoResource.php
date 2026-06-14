<?php

namespace App\Http\Resources\Challenge;

use App\Models\Challenge\ChallengePartnerLogo;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ChallengePartnerLogoResource extends Resource
{
    public int $id;

    public string $url;

    public string $filename;

    public ?string $href;

    public int $order;

    public bool $invert_in_dark;

    public static function fromModel(ChallengePartnerLogo $logo): self
    {
        return self::from([
            'id' => $logo->id,
            'url' => $logo->url,
            'filename' => $logo->filename,
            'href' => $logo->href,
            'order' => $logo->order,
            'invert_in_dark' => $logo->invert_in_dark,
        ]);
    }
}
