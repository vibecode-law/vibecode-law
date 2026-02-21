<?php

namespace App\Enums;

enum VideoPlayerEvent: string
{
    case Playing = 'playing';
    case TimeUpdate = 'timeupdate';
    case Ended = 'ended';
}
