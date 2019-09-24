<?php

namespace Tests\Unit\LaravelJobs\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Jobs\Contacts\ChangeStatusOfContactsWhichWereNotActiveForDays;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class ChangeStatusOfContactsWhichWereNotActiveForDaysTest
 *
 * @package Tests\Unit\LaravelJobs\Contacts
 */
class ChangeStatusOfContactsWhichWereNotActiveForDaysTest extends TestCase
{
    public function testChangeStatusOfContactsWhichWereNotActiveForDaysTest()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'last_active_at' => Carbon::now()->subDays(Contact::INACTIVE_DAYS_COUNT + 10),
        ]);

        (new ChangeStatusOfContactsWhichWereNotActiveForDays())->handle();

        self::assertEquals($contact->latestStatus->status, ContactStatuses::INACTIVE);
    }

    public function testDoNotChangeStatusOfContactsWhichWereActiveTest()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'last_active_at' => Carbon::now()->subDays(Contact::INACTIVE_DAYS_COUNT - 10),
        ]);

        (new ChangeStatusOfContactsWhichWereNotActiveForDays())->handle();

        self::assertEquals($contact->latestStatus->status, ContactStatuses::ACTIVE);
    }
}
