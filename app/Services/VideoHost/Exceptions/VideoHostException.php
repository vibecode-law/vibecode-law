<?php

namespace App\Services\VideoHost\Exceptions;

class VideoHostException extends \RuntimeException
{
    public static function authenticationFailed(?\Throwable $previous = null): self
    {
        return new self(
            message: 'Unable to authenticate with the video host. Please check the API credentials.',
            previous: $previous,
        );
    }

    public static function assetNotFound(?\Throwable $previous = null): self
    {
        return new self(
            message: 'No asset found with that ID. Please check the asset ID and try again.',
            previous: $previous,
        );
    }

    public static function requestFailed(?\Throwable $previous = null): self
    {
        return new self(
            message: 'The video host returned an error. Please try again later.',
            previous: $previous,
        );
    }
}
