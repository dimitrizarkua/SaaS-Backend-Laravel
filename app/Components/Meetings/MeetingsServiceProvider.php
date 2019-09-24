<?php

namespace App\Components\Meetings;

use App\Components\Meetings\Interfaces\MeetingsServiceInterface;
use App\Components\Meetings\Services\MeetingsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class MeetingsServiceProvider
 *
 * @package App\Components\Meetings
 */
class MeetingsServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        MeetingsServiceInterface::class => MeetingsService::class,
    ];
}
