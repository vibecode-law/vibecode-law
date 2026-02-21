<?php

// Laravel Preset - Manually Recreated (without controller method restrictions)

// Traits & Concerns
arch('traits')
    ->expect('App\Traits')
    ->toBeTrait();

arch('concerns')
    ->expect('App\Concerns')
    ->toBeTrait();

// Enums
arch('enums should be in App\Enums')
    ->expect('App')
    ->not->toBeEnums()
    ->ignoring('App\Enums');

arch('App\Enums should only contain enums')
    ->expect('App\Enums')
    ->toBeEnums()
    ->ignoring('App\Enums\Concerns');

// Features
arch('features')
    ->expect('App\Features')
    ->toBeClasses()
    ->toHaveMethod('resolve')
    ->ignoring('App\Features\Concerns');

// Exceptions
arch('exceptions')
    ->expect('App\Exceptions')
    ->toImplement(Throwable::class)
    ->ignoring('App\Exceptions\Handler');

arch('throwables should be in App\Exceptions')
    ->expect('App')
    ->not->toImplement(Throwable::class)
    ->ignoring('App\Exceptions')
    ->ignoring('App\Services\VideoHost\Exceptions');

// Middleware
arch('middleware')
    ->expect('App\Http\Middleware')
    ->toHaveMethod('handle');

// Models
arch('models')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\Scopes');

arch('models should not have Model suffix')
    ->expect('App\Models')
    ->not->toHaveSuffix('Model');

arch('Eloquent models should be in App\Models')
    ->expect('App')
    ->not->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models');

// Form Requests
arch('form requests')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request')
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->toHaveMethod('rules');

arch('form requests should be in App\Http\Requests')
    ->expect('App')
    ->not->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->ignoring('App\Http\Requests');

// Console Commands
arch('commands')
    ->expect('App\Console\Commands')
    ->toHaveSuffix('Command')
    ->toExtend('Illuminate\Console\Command')
    ->toHaveMethod('handle');

// Mail
arch('mail')
    ->expect('App\Mail')
    ->toExtend('Illuminate\Mail\Mailable')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

// Jobs
arch('jobs')
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue')
    ->toHaveMethod('handle');

// Listeners
arch('listeners')
    ->expect('App\Listeners')
    ->toHaveMethod('handle');

// Notifications
arch('notifications')
    ->expect('App\Notifications')
    ->toExtend('Illuminate\Notifications\Notification');

// Providers
arch('providers')
    ->expect('App\Providers')
    ->toHaveSuffix('ServiceProvider')
    ->toExtend('Illuminate\Support\ServiceProvider');

arch('providers should be in App\Providers')
    ->expect('App')
    ->not->toExtend('Illuminate\Support\ServiceProvider')
    ->ignoring('App\Providers');

arch('providers suffix should be in App\Providers')
    ->expect('App')
    ->not->toHaveSuffix('ServiceProvider')
    ->ignoring('App\Providers');

// Controllers (without method restrictions)
arch('controllers suffix should be in App\Http\Controllers')
    ->expect('App')
    ->not->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers');

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('Http namespace should be in App\Http')
    ->expect('App\Http')
    ->toOnlyBeUsedIn('App\Http');

// Debugging functions
arch('debugging functions')
    ->expect(['dd', 'ddd', 'dump', 'env', 'exit', 'ray'])
    ->not->toBeUsed();

// Policies
arch('policies')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

// Attributes
arch('attributes')
    ->expect('App\Attributes')
    ->toImplement('Illuminate\Contracts\Container\ContextualAttribute')
    ->toHaveAttribute('Attribute')
    ->toHaveMethod('resolve');
