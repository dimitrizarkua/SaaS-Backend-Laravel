<?php

namespace Tests\Unit\Messaging;

use App\Components\Messages\Models\MessageParticipantData;
use App\Models\User;
use Faker\Factory as Faker;

/**
 * Class MessageDataMock
 *
 * @package Tests\Unit\Messaging
 */
class MessageDataMock
{

    /**
     * Return Email message instance
     *
     * @return \App\Components\Messages\Models\EmailMessage
     */
    public static function getEmailMessageInstance()
    {
        $faker = Faker::create();

        return new \App\Components\Messages\Models\EmailMessage(
            factory(User::class)->create()->id,
            [
                new MessageParticipantData($faker->email, $faker->name),
            ],
            $faker->word,
            $faker->paragraph
        );
    }
}
