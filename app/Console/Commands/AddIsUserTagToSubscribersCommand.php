<?php

namespace App\Console\Commands;

use App\Jobs\MarketingEmail\AddIsUserTagToSubscriberJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class AddIsUserTagToSubscribersCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:add-is-user-tag-to-subscribers';

    /**
     * @var string
     */
    protected $description = 'Add the is_user tag to all existing user subscribers';

    public function handle(): int
    {
        $tagUuid = Config::get('marketing.is_user_tag_uuid');

        if ($tagUuid === null) {
            $this->components->error(string: 'The marketing.is_user_tag_uuid config value is not set.');

            return Command::FAILURE;
        }

        $users = User::query()
            ->whereNotNull('external_subscriber_uuid')
            ->get();

        if ($users->isEmpty()) {
            $this->components->info(string: 'No subscribers to update.');

            return Command::SUCCESS;
        }

        $this->components->info(string: "Dispatching is_user tag jobs for {$users->count()} subscriber(s)...");

        $progressBar = $this->output->createProgressBar(max: $users->count());
        $progressBar->start();

        foreach ($users as $user) {
            AddIsUserTagToSubscriberJob::dispatch(user: $user);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->components->info(string: "Dispatched is_user tag jobs for {$users->count()} subscriber(s).");

        return Command::SUCCESS;
    }
}
