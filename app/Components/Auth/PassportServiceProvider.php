<?php

namespace App\Components\Auth;

use App\Components\Auth\Interfaces\ForgotPasswordServiceInterface;
use App\Components\Auth\Services\ForgotPasswordService;
use Illuminate\Support\ServiceProvider;

/**
 * Class PassportServiceProvider
 *
 * @package App\Components\Auth
 */
class PassportServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        ForgotPasswordServiceInterface::class => ForgotPasswordService::class,
    ];
}
