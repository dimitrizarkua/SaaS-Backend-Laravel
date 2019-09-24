<?php

namespace App\Components\Notes;

use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Services\NotesService;
use Illuminate\Support\ServiceProvider;

/**
 * Class NotesServiceProvider
 *
 * @package App\Components\Notes
 */
class NotesServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        NotesServiceInterface::class => NotesService::class,
    ];
}
