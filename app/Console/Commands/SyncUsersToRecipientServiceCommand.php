<?php

namespace App\Console\Commands;

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SyncUsersToRecipientServiceCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:sync-marketing-recipients';

    /**
     * @var string
     */
    protected $description = 'Sync existing users to the recipient service';

    public function handle(): int
    {
        $existingSubscribers = User::query()
            ->whereNotNull('external_subscriber_uuid')
            ->count();

        if ($existingSubscribers > 0) {
            $this->components->error(
                string: "Cannot sync: {$existingSubscribers} user(s) already have a subscriber UUID. This command is only for initial sync.",
            );

            return Command::FAILURE;
        }

        $users = User::query()
            ->whereNotNull('email_verified_at')
            ->whereNull('marketing_opt_out_at')
            ->get();

        if ($users->isEmpty()) {
            $this->components->info(string: 'No users to sync.');

            return Command::SUCCESS;
        }

        $isUserTagUuid = Config::get('marketing.is_user_tag_uuid');
        $showcaseTagUuid = Config::get('marketing.has_showcase_tag_uuid');

        $this->components->info(string: "Syncing {$users->count()} user(s) to recipient service...");

        $progressBar = $this->output->createProgressBar(max: $users->count());
        $progressBar->start();

        foreach ($users as $user) {
            $tags = [];

            if ($isUserTagUuid !== null) {
                $tags[] = $isUserTagUuid;
            }

            if ($showcaseTagUuid !== null && $user->showcases()->exists()) {
                $tags[] = $showcaseTagUuid;
            }

            CreateExternalSubscriberJob::dispatch(
                user: $user,
                tags: $tags,
                skipConfirmation: true,
            );

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->components->info(string: "Dispatched sync jobs for {$users->count()} user(s).");

        return Command::SUCCESS;
    }
}
