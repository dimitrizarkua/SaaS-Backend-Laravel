<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\Models\User;

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

$privateChannels = ['job-{jobId}', 'contact-{contactId}', 'user-{userId}'];

foreach ($privateChannels as $channel) {
    Broadcast::channel($channel, function () {
        return auth()->check();
    });
}

$presenceChannels = ['online-job-{jobId}', 'online-contact-{contactId}'];

foreach ($presenceChannels as $channel) {
    Broadcast::channel($channel, function (User $user) {
        $response = [
            'id'        => $user->id,
            'full_name' => $user->full_name,
        ];

        if (null !== $user->avatar) {
            $response['avatar'] = $user->avatar->url;
        }

        return $response;
    });
}
