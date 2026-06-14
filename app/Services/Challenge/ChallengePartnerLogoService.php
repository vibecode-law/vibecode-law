<?php

namespace App\Services\Challenge;

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ChallengePartnerLogoService
{
    public function __construct(private Challenge $challenge) {}

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function store(array $files): void
    {
        $order = (int) $this->challenge->partnerLogos()->max('order');
        $storagePath = "challenge/{$this->challenge->id}/partner-logos";

        foreach ($files as $file) {
            $path = $file->storeAs(
                path: $storagePath,
                name: Str::uuid().'.'.$file->getClientOriginalExtension(),
                options: 'public',
            );

            $this->challenge->partnerLogos()->create([
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'order' => ++$order,
            ]);
        }
    }

    /**
     * @param  array<int, array{id: int, order: int}>  $items
     */
    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            ChallengePartnerLogo::query()
                ->where('id', $item['id'])
                ->where('challenge_id', $this->challenge->id)
                ->update(['order' => $item['order']]);
        }
    }

    public function delete(ChallengePartnerLogo $logo): void
    {
        $logo->delete();
    }
}
