<?php

use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\SubChallenge;
use App\Models\Organisation\Organisation;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('show returns active challenge', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('challenge/public/show')
            ->has('challenge')
            ->has('showcases')
            ->has('participants')
        );
});

test('show returns 404 for inactive challenge', function () {
    $challenge = Challenge::factory()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertNotFound();
});

test('show returns correct challenge data structure', function () {
    $organisation = Organisation::factory()->create();
    $challenge = Challenge::factory()
        ->ongoing()
        ->forOrganisation($organisation)
        ->create([
            'description' => '**description**',
        ])
        ->fresh();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('challenge', fn (AssertableInertia $c) => $c
                ->where('id', $challenge->id)
                ->where('slug', $challenge->slug)
                ->where('title', $challenge->title)
                ->where('tagline', $challenge->tagline)
                ->missing('description')
                ->where('description_html', "<p><strong>description</strong></p>\n")
                ->missing('involvement_instructions')
                ->where('involvement_instructions_html', null)
                ->missing('participant_instructions')
                ->where('participant_instructions_html', null)
                ->where('starts_at', $challenge->starts_at->toIso8601String())
                ->where('ends_at', $challenge->ends_at->toIso8601String())
                ->where('timezone', $challenge->timezone)
                ->where('is_active', true)
                ->where('is_featured', false)
                ->where('thumbnail_url', $challenge->thumbnail_url)
                ->where('thumbnail_rect_strings', $challenge->thumbnail_rect_strings)
                ->has('organisation', fn (AssertableInertia $o) => $o
                    ->where('id', $organisation->id)
                    ->where('name', $organisation->name)
                    ->where('tagline', $organisation->tagline)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                )
                ->where('sub_challenges', [])
            )
        );
});

test('show uses slug for route model binding', function () {
    Challenge::factory()->active()->create([
        'slug' => 'unique-slug-123',
    ]);

    get('/inspiration/challenges/unique-slug-123')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('challenge.slug', 'unique-slug-123')
        );
});

test('show returns 404 for non-existent challenge', function () {
    get('/inspiration/challenges/non-existent-slug')
        ->assertNotFound();
});

test('show returns showcases ordered by upvotes descending', function () {
    $challenge = Challenge::factory()->active()->create();
    $upvoters = User::factory()->count(5)->create();

    $mostUpvoted = Showcase::factory()->approved()->create(['title' => 'Most Upvoted']);
    $mostUpvoted->upvoters()->attach($upvoters->pluck('id'));

    $middleUpvoted = Showcase::factory()->approved()->create(['title' => 'Middle Upvoted']);
    $middleUpvoted->upvoters()->attach($upvoters->take(2)->pluck('id'));

    $leastUpvoted = Showcase::factory()->approved()->create(['title' => 'Least Upvoted']);

    $challenge->showcases()->attach([
        $mostUpvoted->id,
        $middleUpvoted->id,
        $leastUpvoted->id,
    ]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 3)
            ->where('showcases.0.id', $mostUpvoted->id)
            ->where('showcases.1.id', $middleUpvoted->id)
            ->where('showcases.2.id', $leastUpvoted->id)
        );
});

test('show returns correct showcase data structure', function () {
    $challenge = Challenge::factory()->active()->create();

    $showcase = Showcase::factory()->approved()->create([
        'title' => 'Test Showcase',
        'slug' => 'test-showcase',
        'tagline' => 'Test tagline',
        'thumbnail_extension' => 'jpg',
        'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
    ]);

    $challenge->showcases()->attach($showcase);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $s) => $s
                ->where('id', $showcase->id)
                ->where('slug', 'test-showcase')
                ->where('title', 'Test Showcase')
                ->where('tagline', 'Test tagline')
                ->has('thumbnail_url')
                ->where('thumbnail_rect_string', 'rect=10,20,100,100')
                ->where('upvotes_count', 0)
                ->missing('has_upvoted')
                ->has('view_count')
                ->has('user')
                ->where('sub_challenge_id', null)
            )
        );
});

test('show includes has_upvoted for authenticated users', function () {
    $challenge = Challenge::factory()->active()->create();
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()->approved()->create();
    $showcase->upvoters()->attach($user);

    $challenge->showcases()->attach($showcase);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.0.has_upvoted', true)
            ->where('showcases.0.upvotes_count', 1)
        );
});

test('show has_upvoted is false when authenticated user has not upvoted', function () {
    $challenge = Challenge::factory()->active()->create();
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()->approved()->create();
    $challenge->showcases()->attach($showcase);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.0.has_upvoted', false)
        );
});

test('show excludes has_upvoted for guests', function () {
    $challenge = Challenge::factory()->active()->create();

    $showcase = Showcase::factory()->approved()->create();
    $challenge->showcases()->attach($showcase);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $s) => $s
                ->missing('has_upvoted')
                ->has('id')
                ->has('slug')
                ->has('title')
                ->has('tagline')
                ->has('thumbnail_url')
                ->has('thumbnail_rect_string')
                ->has('upvotes_count')
                ->has('view_count')
                ->has('user')
                ->where('sub_challenge_id', null)
            )
        );
});

test('show only includes publicly visible showcases', function () {
    $challenge = Challenge::factory()->active()->create();

    $approved = Showcase::factory()->approved()->create();
    $draft = Showcase::factory()->draft()->create();
    $pending = Showcase::factory()->pending()->create();
    $rejected = Showcase::factory()->rejected()->create();

    $challenge->showcases()->attach([
        $approved->id,
        $draft->id,
        $pending->id,
        $rejected->id,
    ]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 1)
            ->where('showcases.0.id', $approved->id)
        );
});

test('show returns organisation when present', function () {
    $organisation = Organisation::factory()->create();
    $challenge = Challenge::factory()
        ->active()
        ->forOrganisation($organisation)
        ->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('challenge.organisation', fn (AssertableInertia $o) => $o
                ->where('id', $organisation->id)
                ->where('name', $organisation->name)
                ->where('tagline', $organisation->tagline)
                ->where('thumbnail_url', null)
                ->where('thumbnail_rect_strings', null)
            )
        );
});

test('show returns null organisation when absent', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('challenge.organisation', null)
        );
});

test('show returns empty showcases array when none exist', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 0)
        );
});

test('show returns unique participants from publicly visible showcases', function () {
    $challenge = Challenge::factory()->active()->create();
    $user = User::factory()->create(['first_name' => 'Alice']);

    $showcase1 = Showcase::factory()->approved()->for($user)->create();
    $showcase2 = Showcase::factory()->approved()->for($user)->create();
    $showcase3 = Showcase::factory()->approved()->create();

    $challenge->showcases()->attach([$showcase1->id, $showcase2->id, $showcase3->id]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('participants', 2)
            ->where('participants', fn ($participants) => $participants->contains('first_name', 'Alice'))
            ->has('participants.0', fn (AssertableInertia $p) => $p
                ->has('first_name')
                ->has('avatar')
                ->has('handle')
            )
        );
});

test('show redirects to login for invite-to-view-and-submit challenge as guest', function () {
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertRedirect(route('login'));
});

test('show stores intended url when redirecting guest to login', function () {
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();

    get(route('inspiration.challenges.show', $challenge));

    expect(session('url.intended'))->toBe(route('inspiration.challenges.show', $challenge));
});

test('show renders invite-only page for invite-to-view-and-submit challenge without invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('challenge/invite-only')
            ->missing('challenge')
        );
});

test('show returns 200 for invite-to-view-and-submit challenge with accepted invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertOk();
});

test('show canSubmit is true for public challenges', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', true)
        );
});

test('show canSubmit is false when challenge has not started yet', function () {
    $challenge = Challenge::factory()->upcoming()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
        );
});

test('show canSubmit is false when challenge close date has passed', function () {
    $challenge = Challenge::factory()->active()->create([
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->subDay(),
    ]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
        );
});

test('show isEligibleToSubmit is true for public challenge', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('isEligibleToSubmit', true)
        );
});

test('show isEligibleToSubmit is true for upcoming invite-to-submit challenge with ViewAndSubmit invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->upcoming()->inviteToSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
        'scope' => InviteCodeScope::ViewAndSubmit,
    ]);
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
            ->where('isEligibleToSubmit', true)
        );
});

test('show isEligibleToSubmit is false for invite-to-submit challenge without invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('isEligibleToSubmit', false)
        );
});

test('show canSubmit is false for invite-to-submit challenge without invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
        );
});

test('show canSubmit is true for invite-to-submit challenge with ViewAndSubmit invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
        'scope' => InviteCodeScope::ViewAndSubmit,
    ]);
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', true)
        );
});

test('show returns 200 for invite-to-submit challenge as guest', function () {
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertOk();
});

test('show canSubmit is false for invite-to-submit challenge with View-only invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
        );
});

test('show canSubmit is false for invite-to-view-and-submit challenge with View-only invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
        );
});

test('show canSubmit is true for invite-to-view-and-submit challenge with ViewAndSubmit invite', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
        'scope' => InviteCodeScope::ViewAndSubmit,
    ]);
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', true)
        );
});

test('show canSubmit is false for guest on invite-to-submit challenge', function () {
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
        );
});

test('show returns 200 for invite-to-view-and-submit challenge when user is admin', function () {
    /** @var User */
    $user = User::factory()->admin()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertOk();
});

test('show canSubmit is false for admin on invite-to-view-and-submit challenge without invite', function () {
    /** @var User */
    $user = User::factory()->admin()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
            ->where('isEligibleToSubmit', false)
        );
});

test('show canSubmit is false for admin on invite-to-submit challenge without invite', function () {
    /** @var User */
    $user = User::factory()->admin()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', false)
            ->where('isEligibleToSubmit', false)
        );
});

test('show canSubmit is true for admin with ViewAndSubmit invite', function () {
    /** @var User */
    $user = User::factory()->admin()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
        'scope' => InviteCodeScope::ViewAndSubmit,
    ]);
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canSubmit', true)
        );
});

test('show requiresInviteToSubmit is false for public challenge', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('requiresInviteToSubmit', false)
        );
});

test('show requiresInviteToSubmit is true for invite-to-submit challenge', function () {
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('requiresInviteToSubmit', true)
        );
});

test('show requiresInviteToSubmit is true for invite-to-view-and-submit challenge', function () {
    /** @var User */
    $user = User::factory()->admin()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('requiresInviteToSubmit', true)
        );
});

test('show renders participant_instructions_html for guest on public challenge', function () {
    $challenge = Challenge::factory()->active()->create([
        'participant_instructions' => '**join**',
    ]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('challenge.participant_instructions_html', "<p><strong>join</strong></p>\n")
            ->missing('challenge.participant_instructions')
        );
});

test('show hides participant_instructions_html from ineligible invite-to-submit viewer', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create([
        'participant_instructions' => '**secret**',
    ]);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('isEligibleToSubmit', false)
            ->missing('challenge.participant_instructions_html')
            ->missing('challenge.participant_instructions')
        );
});

test('show renders participant_instructions_html for eligible invite-to-submit viewer', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create([
        'participant_instructions' => '**members only**',
    ]);
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
        'scope' => InviteCodeScope::ViewAndSubmit,
    ]);
    $inviteCode->users()->attach($user);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('challenge.participant_instructions_html', "<p><strong>members only</strong></p>\n")
        );
});

test('show renders involvement_instructions_html for invite-to-submit challenge', function () {
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create([
        'involvement_instructions' => '**enter**',
    ]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('challenge.involvement_instructions_html', "<p><strong>enter</strong></p>\n")
            ->missing('challenge.involvement_instructions')
        );
});

test('show sets intended url for guest on invite-to-submit challenge', function () {
    $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

    get(route('inspiration.challenges.show', $challenge));

    expect(session('url.intended'))->toBe(route('inspiration.challenges.show', $challenge));
});

test('show does not set intended url for guest on public challenge', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge));

    expect(session('url.intended'))->toBeNull();
});

test('show excludes participants from non-visible showcases', function () {
    $challenge = Challenge::factory()->active()->create();

    $approved = Showcase::factory()->approved()->create();
    $draft = Showcase::factory()->draft()->create();

    $challenge->showcases()->attach([$approved->id, $draft->id]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('participants', 1)
        );
});

test('show returns sub-challenges ordered on the challenge', function () {
    $challenge = Challenge::factory()->active()->create();
    $second = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 2]);
    $first = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 1]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('challenge.sub_challenges', 2)
            ->has('challenge.sub_challenges.0', fn (AssertableInertia $sub) => $sub
                ->where('id', $first->id)
                ->where('name', $first->name)
                ->where('tagline', $first->tagline)
                ->where('description', $first->description)
                ->where('order', $first->order)
            )
            ->where('challenge.sub_challenges.1.id', $second->id)
        );
});

test('show exposes each showcase sub-challenge id from the pivot', function () {
    $challenge = Challenge::factory()->active()->create();
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

    $showcase = Showcase::factory()->approved()->create();
    $challenge->showcases()->attach($showcase, ['sub_challenge_id' => $subChallenge->id]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.0.id', $showcase->id)
            ->where('showcases.0.sub_challenge_id', $subChallenge->id)
        );
});
