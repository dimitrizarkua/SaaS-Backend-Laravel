<?php

namespace App\Components\Search\Observers;

use App\Components\Notifications\Enums\NotificationSettingTypes;
use App\Components\Notifications\Models\UserNotificationSetting;
use App\Components\Search\Models\UserAndTeam;
use App\Models\User;

/**
 * Class UserObserver
 *
 * @package App\Http\Requests\Search\Observers
 */
class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  User $user
     *
     * @return void
     */
    public function created(User $user)
    {
        $this->updateIndex($user);
        $this->setDefaultNotificationSettings($user);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  User $user
     *
     * @return void
     */
    public function updated(User $user)
    {
        $this->updateIndex($user);
    }

    /**
     * Handle the User "deleting" event.
     *
     * @param  User $user
     *
     * @return void
     */
    public function deleting(User $user)
    {
        $this->getUserQuery($user)->unsearchable();
    }

    /**
     * Updates index.
     *
     * @param \App\Models\User $user
     */
    private function updateIndex(User $user): void
    {
        $this->getUserQuery($user)->searchable();
    }

    /**
     * Sets default user notification settings.
     *
     * @see SN-313.
     *
     * There is a new (unassigned) job+
     * A job is assigned to someone else+
     * Notify me when someone replies to an unassigned job,
     * Notify me when someone replies to a job owned by someone else,
     * Notify me when someone adds a note to an unassigned job,
     * Notify me when someone adds to a job owned by someone else
     *
     * @param \App\Models\User $user
     */
    private function setDefaultNotificationSettings(User $user): void
    {
        $defaultTypes = [
            NotificationSettingTypes::JOB_CREATED,
            NotificationSettingTypes::JOB_ASSIGNED_TO_SOMEONE,
            NotificationSettingTypes::MESSAGE_ADDED_TO_UNASSIGNED_JOB,
            NotificationSettingTypes::NOTE_ADDED_TO_UNASSIGNED_JOB,
            NotificationSettingTypes::MESSAGE_ADDED_TO_JOB_OWNED_BY_SOMEONE,
            NotificationSettingTypes::NOTE_ADDED_TO_JOB_OWNED_BY_SOMEONE,
        ];

        $insertData   = [];
        foreach ($defaultTypes as $type) {
            $insertData[] = [
                'user_id' => $user->id,
                'type'    => $type,
                'value'   => false,
            ];
        }

        UserNotificationSetting::insert($insertData);
    }

    /**
     * Returns query to get user record from view.
     *
     * @param \App\Models\User $user
     *
     * @return \App\Components\Search\Models\UserAndTeam
     */
    private function getUserQuery(User $user)
    {
        return UserAndTeam::where('type', UserAndTeam::TYPE_USER)
            ->where('entity_id', $user->id);
    }
}
