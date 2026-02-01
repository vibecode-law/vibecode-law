<?php

use App\Services\MarketingEmail\Recipients\MailcoachRecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\RecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\UpdateRecipientData;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Spatie\MailcoachSdk\Resources\Subscriber;

function createMockSubscriber(array $attributes = []): Subscriber
{
    $defaults = [
        'uuid' => 'subscriber-uuid-123',
        'email_list_uuid' => 'list-uuid-456',
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'extra_attributes' => ['company' => 'Acme'],
        'tags' => ['newsletter', 'vip'],
        'subscribed_at' => '2024-01-15T10:00:00Z',
        'unsubscribed_at' => null,
        'created_at' => '2024-01-01T08:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    return new Subscriber(array_merge($defaults, $attributes));
}

describe('createRecipient', function () {
    it('creates a subscriber with required fields only', function () {
        $subscriber = createMockSubscriber(attributes: ['uuid' => 'new-subscriber-uuid']);

        Mailcoach::shouldReceive('createSubscriber')
            ->once()
            ->with(
                'list-uuid',
                ['email' => 'new@example.com']
            )
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $result = $service->createRecipient(data: new CreateRecipientData(
            email: 'new@example.com',
            listId: 'list-uuid',
        ));

        expect($result)->toBe('new-subscriber-uuid');
    });

    it('creates a subscriber with all attributes', function () {
        $subscriber = createMockSubscriber(attributes: ['uuid' => 'full-subscriber-uuid']);

        Mailcoach::shouldReceive('createSubscriber')
            ->once()
            ->with(
                'list-uuid',
                [
                    'email' => 'full@example.com',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'extra_attributes' => ['role' => 'admin'],
                    'tags' => ['premium'],
                ]
            )
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $result = $service->createRecipient(data: new CreateRecipientData(
            email: 'full@example.com',
            listId: 'list-uuid',
            firstName: 'Jane',
            lastName: 'Smith',
            extraAttributes: ['role' => 'admin'],
            tags: ['premium'],
        ));

        expect($result)->toBe('full-subscriber-uuid');
    });

    it('filters out null and empty values from attributes', function () {
        $subscriber = createMockSubscriber();

        Mailcoach::shouldReceive('createSubscriber')
            ->once()
            ->with(
                'list-uuid',
                [
                    'email' => 'test@example.com',
                    'first_name' => 'John',
                ]
            )
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $service->createRecipient(data: new CreateRecipientData(
            email: 'test@example.com',
            listId: 'list-uuid',
            firstName: 'John',
            lastName: null,
            extraAttributes: [],
            tags: [],
        ));
    });
});

describe('updateRecipient', function () {
    it('updates subscriber with provided attributes', function () {
        Mailcoach::shouldReceive('updateSubscriber')
            ->once()
            ->with(
                'subscriber-uuid-123',
                [
                    'email' => 'updated@example.com',
                    'first_name' => 'Updated',
                ]
            );

        $service = new MailcoachRecipientService;
        $service->updateRecipient(
            externalId: 'subscriber-uuid-123',
            data: new UpdateRecipientData(
                email: 'updated@example.com',
                firstName: 'Updated',
            ),
        );
    });

    it('filters out null values when updating', function () {
        Mailcoach::shouldReceive('updateSubscriber')
            ->once()
            ->with(
                'subscriber-uuid-123',
                ['first_name' => 'Only Name']
            );

        $service = new MailcoachRecipientService;
        $service->updateRecipient(
            externalId: 'subscriber-uuid-123',
            data: new UpdateRecipientData(
                firstName: 'Only Name',
            ),
        );
    });

    it('includes all non-null attributes', function () {
        Mailcoach::shouldReceive('updateSubscriber')
            ->once()
            ->with(
                'subscriber-uuid-123',
                [
                    'email' => 'new@example.com',
                    'first_name' => 'First',
                    'last_name' => 'Last',
                    'extra_attributes' => ['key' => 'value'],
                    'tags' => ['tag1', 'tag2'],
                ]
            );

        $service = new MailcoachRecipientService;
        $service->updateRecipient(
            externalId: 'subscriber-uuid-123',
            data: new UpdateRecipientData(
                email: 'new@example.com',
                firstName: 'First',
                lastName: 'Last',
                extraAttributes: ['key' => 'value'],
                tags: ['tag1', 'tag2'],
            ),
        );
    });
});

describe('deleteRecipient', function () {
    it('deletes subscriber by external id', function () {
        Mailcoach::shouldReceive('deleteSubscriber')
            ->once()
            ->with('subscriber-uuid-to-delete');

        $service = new MailcoachRecipientService;
        $service->deleteRecipient(externalId: 'subscriber-uuid-to-delete');
    });
});

describe('getRecipient', function () {
    it('retrieves subscriber and maps to RecipientData', function () {
        $subscriber = createMockSubscriber(attributes: [
            'uuid' => 'get-subscriber-uuid',
            'email' => 'get@example.com',
            'first_name' => 'Get',
            'last_name' => 'User',
            'extra_attributes' => ['company' => 'TestCo'],
            'tags' => ['active'],
            'subscribed_at' => '2024-06-01T12:00:00Z',
            'unsubscribed_at' => null,
            'created_at' => '2024-05-01T08:00:00Z',
            'updated_at' => '2024-06-01T12:00:00Z',
        ]);

        Mailcoach::shouldReceive('subscriber')
            ->once()
            ->with('get-subscriber-uuid')
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $result = $service->getRecipient(externalId: 'get-subscriber-uuid');

        expect($result)->toBeInstanceOf(RecipientData::class);
        expect($result->externalId)->toBe('get-subscriber-uuid');
        expect($result->email)->toBe('get@example.com');
        expect($result->firstName)->toBe('Get');
        expect($result->lastName)->toBe('User');
        expect($result->extraAttributes)->toBe(['company' => 'TestCo']);
        expect($result->tags)->toBe(['active']);
        expect($result->subscribedAt->toIso8601String())->toBe('2024-06-01T12:00:00+00:00');
        expect($result->unsubscribedAt)->toBeNull();
        expect($result->createdAt->toIso8601String())->toBe('2024-05-01T08:00:00+00:00');
        expect($result->updatedAt->toIso8601String())->toBe('2024-06-01T12:00:00+00:00');
    });

    it('handles subscriber with null timestamps', function () {
        $subscriber = createMockSubscriber(attributes: [
            'subscribed_at' => null,
            'unsubscribed_at' => null,
            'created_at' => null,
            'updated_at' => null,
        ]);

        Mailcoach::shouldReceive('subscriber')
            ->once()
            ->with('subscriber-uuid-123')
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $result = $service->getRecipient(externalId: 'subscriber-uuid-123');

        expect($result->subscribedAt)->toBeNull();
        expect($result->unsubscribedAt)->toBeNull();
        expect($result->createdAt)->toBeNull();
        expect($result->updatedAt)->toBeNull();
    });
});

describe('findRecipientByEmail', function () {
    it('finds subscriber by email and returns RecipientData', function () {
        $subscriber = createMockSubscriber(attributes: [
            'uuid' => 'found-uuid',
            'email' => 'find@example.com',
        ]);

        Mailcoach::shouldReceive('findByEmail')
            ->once()
            ->with('list-uuid', 'find@example.com')
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $result = $service->findRecipientByEmail(
            email: 'find@example.com',
            listId: 'list-uuid',
        );

        expect($result)->toBeInstanceOf(RecipientData::class);
        expect($result->externalId)->toBe('found-uuid');
        expect($result->email)->toBe('find@example.com');
    });

    it('returns null when subscriber not found', function () {
        Mailcoach::shouldReceive('findByEmail')
            ->once()
            ->with('list-uuid', 'notfound@example.com')
            ->andReturnNull();

        $service = new MailcoachRecipientService;
        $result = $service->findRecipientByEmail(
            email: 'notfound@example.com',
            listId: 'list-uuid',
        );

        expect($result)->toBeNull();
    });
});

describe('confirmRecipient', function () {
    it('confirms subscriber by external id', function () {
        Mailcoach::shouldReceive('confirmSubscriber')
            ->once()
            ->with('subscriber-to-confirm');

        $service = new MailcoachRecipientService;
        $service->confirmRecipient(externalId: 'subscriber-to-confirm');
    });
});

describe('unsubscribeRecipient', function () {
    it('unsubscribes subscriber by external id', function () {
        Mailcoach::shouldReceive('unsubscribeSubscriber')
            ->once()
            ->with('subscriber-to-unsubscribe');

        $service = new MailcoachRecipientService;
        $service->unsubscribeRecipient(externalId: 'subscriber-to-unsubscribe');
    });
});

describe('resubscribeRecipient', function () {
    it('resubscribes subscriber by external id', function () {
        Mailcoach::shouldReceive('resubscribeSubscriber')
            ->once()
            ->with('subscriber-to-resubscribe');

        $service = new MailcoachRecipientService;
        $service->resubscribeRecipient(externalId: 'subscriber-to-resubscribe');
    });
});

describe('addTags', function () {
    it('adds tags to subscriber', function () {
        $subscriber = Mockery::mock(Subscriber::class);
        $subscriber->shouldReceive('addTags')
            ->once()
            ->with(['new-tag', 'another-tag']);

        Mailcoach::shouldReceive('subscriber')
            ->once()
            ->with('subscriber-uuid-for-tags')
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $service->addTags(
            externalId: 'subscriber-uuid-for-tags',
            tags: ['new-tag', 'another-tag'],
        );
    });
});

describe('removeTags', function () {
    it('removes tags from subscriber', function () {
        $subscriber = Mockery::mock(Subscriber::class);
        $subscriber->shouldReceive('removeTags')
            ->once()
            ->with(['tag-to-remove']);

        Mailcoach::shouldReceive('subscriber')
            ->once()
            ->with('subscriber-uuid-for-tag-removal')
            ->andReturn($subscriber);

        $service = new MailcoachRecipientService;
        $service->removeTags(
            externalId: 'subscriber-uuid-for-tag-removal',
            tags: ['tag-to-remove'],
        );
    });
});
