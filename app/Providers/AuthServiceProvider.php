<?php

namespace App\Providers;

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Policies\AssessmentReportPolicy;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Policies\JobEquipmentPolicy;
use App\Components\Notifications\Policies\UserNotificationPolicy;
use App\Components\Jobs\Policies\TeamMemberPolicy;
use App\Components\Meetings\Models\Meeting;
use App\Components\Meetings\Policies\MeetingPolicy;
use App\Components\Messages\Models\Message;
use App\Components\Messages\Policies\MessagePolicy;
use App\Components\Notes\Models\Note;
use App\Components\Notes\Policies\NotePolicy;
use App\Components\Notifications\Models\UserNotification;
use App\Components\RBAC\Authorization;
use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use App\Components\Teams\Models\Team;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

/**
 * Class AuthServiceProvider
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Note::class             => NotePolicy::class,
        Meeting::class          => MeetingPolicy::class,
        Team::class             => TeamMemberPolicy::class,
        Message::class          => MessagePolicy::class,
        UserNotification::class => UserNotificationPolicy::class,
        JobEquipment::class     => JobEquipmentPolicy::class,
        AssessmentReport::class => AssessmentReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerRBACPolicies();

        Passport::routes(null, ['middleware' => 'passport-api']);
        Passport::tokensExpireIn(now()->addMinutes(25));
        Passport::refreshTokensExpireIn(now()->addHours(2));

        if (env('PASSPORT_KEYS_STORAGE')) {
            Passport::loadKeysFrom(env('PASSPORT_KEYS_STORAGE'));
        }
    }

    private function registerRBACPolicies(): void
    {
        /** @var \App\Components\RBAC\Interfaces\PermissionDataProviderInterface $permissionsProvider */
        $permissionsProvider = $this->app->make(PermissionDataProviderInterface::class);

        /** @var \Illuminate\Support\Collection $allPermissions */
        $allPermissions = $permissionsProvider->getAllPermissions();

        /** @var \App\Components\RBAC\Models\Permission $permission */
        foreach ($allPermissions as $permission) {
            $name = $permission->getName();

            Gate::define($name, $this->makeAuthCallback($name));
        }
    }

    /**
     * Helper method that creates callback that checks RBAC rule for the user.
     *
     * @param string $permissionName Permission name.
     *
     * @return callable
     */
    private function makeAuthCallback(string $permissionName): callable
    {
        return function ($user, ...$arguments) use ($permissionName) {
            return $this->app->make(Authorization::class)
                ->checkPermission($user, $permissionName, $arguments);
        };
    }
}
