<?php

namespace App\Notifications;

use App\Concerns\ThrottlesMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable, ThrottlesMail;

    /**
     * @var int
     */
    public $tries = 5;
}
