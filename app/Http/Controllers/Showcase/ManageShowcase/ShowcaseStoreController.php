<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Actions\Showcase\SubmitShowcaseAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Showcase\ShowcaseWriteRequest;
use App\Jobs\MarketingEmail\AddShowcaseTagToSubscriberJob;
use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Services\Showcase\ShowcaseMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ShowcaseStoreController extends BaseController
{
    public function __invoke(ShowcaseWriteRequest $request, ShowcaseMediaService $mediaService): RedirectResponse
    {
        $slug = $this->generateSlugFromTitle(title: $request->validated('title'));

        /** @var Showcase */
        $showcase = $request->user()->showcases()->create([
            ...$request->safe()->except(['images', 'practice_area_ids', 'thumbnail', 'remove_thumbnail', 'submit', 'challenge_id']),
            'slug' => $slug,
        ]);

        $showcase->practiceAreas()->sync($request->validated()['practice_area_ids']);

        if ($request->validated('challenge_id') !== null) {
            $showcase->challenges()->attach($request->validated('challenge_id'));
        }

        if ($request->hasFile('thumbnail')) {
            $mediaService->storeThumbnail(
                model: $showcase,
                file: $request->file('thumbnail'),
                crop: $request->validated('thumbnail_crop'),
            );
        }

        if ($request->hasFile('images')) {
            $mediaService->storeImages(model: $showcase, files: $request->file('images'));
        }

        $this->dispatchShowcaseTagJobIfFirstShowcase(user: $request->user());

        if ($request->boolean('submit') === true) {
            app(SubmitShowcaseAction::class)->submit(showcase: $showcase);

            return Redirect::route('user-area.showcases.index')->with('flash', [
                'message' => ['message' => 'Showcase created and submitted for approval.', 'type' => 'success'],
            ]);
        }

        return Redirect::route('showcase.manage.edit', $showcase)->with('flash', [
            'message' => ['message' => 'Showcase created successfully.', 'type' => 'success'],
        ]);
    }

    private function generateSlugFromTitle(string $title): string
    {
        $slug = Str::slug($title);
        // Truncate to 60 chars to leave room for the 7-char suffix (-XXXXXX)
        $slug = Str::limit(value: $slug, limit: 60, end: '');
        $randomSuffix = random_int(min: 100000, max: 999999);

        return $slug.'-'.$randomSuffix;
    }

    private function dispatchShowcaseTagJobIfFirstShowcase(User $user): void
    {
        if ($user->external_subscriber_uuid === null) {
            return;
        }

        $isFirstShowcase = $user->showcases()->count() === 1;

        if ($isFirstShowcase === false) {
            return;
        }

        AddShowcaseTagToSubscriberJob::dispatch(user: $user);
    }
}
