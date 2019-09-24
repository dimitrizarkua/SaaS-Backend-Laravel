<?php

namespace Tests\Unit\Reporting\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactNote;
use App\Components\Contacts\Models\ContactStatus;
use App\Components\Contacts\Models\ContactTag;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Components\Contacts\Models\ManagedAccount;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Components\Notes\Models\Note;
use App\Components\Reporting\Models\Filters\ContactVolumeReportFilter;
use App\Components\Reporting\Services\ContactVolumeReportService;
use App\Helpers\Decimal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Unit\Finance\InvoicesTestFactory;
use Illuminate\Container\Container;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class ContactVolumeReportServiceTest
 *
 * @package Tests\Unit\Reporting\Services
 * @group   contacts
 * @group   reporting
 * @group   reporting-contacts-volume
 */
class ContactVolumeReportServiceTest extends TestCase
{
    use JobFaker;
    /**
     * @var \App\Components\Reporting\Services\ContactVolumeReportService
     */
    private $contactVolumeReportService;

    /** @var Carbon */
    private $yesterday;

    /** @var Carbon */
    private $tomorrow;

    /** @var Carbon */
    private $now;

    public function setUp()
    {
        parent::setUp();

        $this->contactVolumeReportService = Container::getInstance()
            ->make(ContactVolumeReportService::class);

        $this->now       = Carbon::now();
        $this->yesterday = $this->now->copy()->subDay();
        $this->tomorrow  = $this->now->copy()->addDay();
    }

    /**
     * @throws \Exception
     */
    public function testManagedAccountCounterByStaffId(): void
    {
        $managedContactsCount = $this->faker->numberBetween(1, 3);
        $contacts             = factory(Contact::class, $managedContactsCount)->create();
        $user                 = factory(User::class)->create();

        foreach ($contacts as $contact) {
            factory(ManagedAccount::class)->create([
                'user_id'    => $user->id,
                'contact_id' => $contact->id,
                'created_at' => $this->now,
            ]);
        }

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals($managedContactsCount, $reportData->managed);
        self::assertEquals($managedContactsCount, $reportData->staff[0]['managed']);
    }

    /**
     * @throws \Exception
     */
    public function testManagedAccountCounterByEmptyData(): void
    {
        $managedContactsCount = $this->faker->numberBetween(1, 3);
        $contacts             = factory(Contact::class, $managedContactsCount)->create();

        $location = factory(Location::class)->create();

        foreach ($contacts as $contact) {
            factory(ManagedAccount::class)->create([
                'contact_id' => $contact->id,
                'created_at' => $this->yesterday,
            ]);
        }

        $filter              = new ContactVolumeReportFilter();
        $filter->date_from   = $this->now;
        $filter->date_to     = $this->tomorrow;
        $filter->location_id = $location->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals(0, $reportData->managed);
        self::assertEquals(0, $reportData->newLeads);
        self::assertEquals(0, $reportData->touched);
        self::assertEquals(0, $reportData->converted);
        self::assertEquals(0, $reportData->revenue);
        self::assertEmpty($reportData->chart);
    }

    /**
     * @throws \Exception
     */
    public function testManagedAccountCounterByLocation(): void
    {
        $managedContactsCountFirstUser = $this->faker->numberBetween(1, 3);
        $contactsFirstUser             = factory(Contact::class, $managedContactsCountFirstUser)->create();
        $user1                         = factory(User::class)->create();
        $location                      = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $user1->id,
            'location_id' => $location->id,
        ]);

        foreach ($contactsFirstUser as $contact) {
            factory(ManagedAccount::class)->create([
                'user_id'    => $user1->id,
                'contact_id' => $contact->id,
                'created_at' => $this->now,
            ]);
        }

        $managedContactsCountSecondUser = $this->faker->numberBetween(1, 3);
        $contactsSecondUser             = factory(Contact::class, $managedContactsCountSecondUser)->create();
        $user2                          = factory(User::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $user2->id,
            'location_id' => $location->id,
        ]);

        foreach ($contactsSecondUser as $contact) {
            factory(ManagedAccount::class)->create([
                'user_id'    => $user2->id,
                'contact_id' => $contact->id,
                'created_at' => $this->now,
            ]);
        }

        $filter              = new ContactVolumeReportFilter();
        $filter->date_from   = $this->now;
        $filter->date_to     = $this->tomorrow;
        $filter->location_id = $location->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals(
            $managedContactsCountFirstUser + $managedContactsCountSecondUser,
            $reportData->managed
        );
    }

    /**
     * @throws \Exception
     */
    public function testManagedAccountCounterWithOutOfRangeManagedAccount(): void
    {
        $user = factory(User::class)->create();

        $outOfRangeContact = factory(Contact::class)->create();
        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $outOfRangeContact->id,
            'created_at' => $this->yesterday,
        ]);

        $managedContactsCount = $this->faker->numberBetween(1, 3);
        $contacts             = factory(Contact::class, $managedContactsCount)->create();

        foreach ($contacts as $contact) {
            factory(ManagedAccount::class)->create([
                'user_id'    => $user->id,
                'contact_id' => $contact->id,
                'created_at' => $this->now,
            ]);
        }

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);
        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals($managedContactsCount, $reportData->managed);
    }

    /**
     * @throws \Exception
     */
    public function testNewLeadsCounter(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->yesterday,
        ]);

        // $contactStatusActiveYesterday
        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::ACTIVE,
            'contact_id' => $contact->id,
            'created_at' => $this->yesterday,
        ]);

        // $contactStatusActiveToday
        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::ACTIVE,
            'contact_id' => $contact->id,
        ]);

        // $contactStatusLead
        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::LEAD,
            'contact_id' => $contact->id,
            'created_at' => $this->tomorrow,
        ]);

        $user = factory(User::class)->create();

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
            'created_at' => $this->now,
        ]);

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals(1, $reportData->newLeads);
    }

    /**
     * @throws \Exception
     */
    public function testNewLeadsCounterContactWasLeadBefore(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->yesterday,
        ]);

        // $contactStatusLeadYesterday
        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::LEAD,
            'contact_id' => $contact->id,
            'created_at' => $this->yesterday,
        ]);

        // $contactStatusLeadTomorrow
        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::LEAD,
            'contact_id' => $contact->id,
            'created_at' => $this->tomorrow,
        ]);

        $user = factory(User::class)->create();

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
        ]);

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals(0, $reportData->newLeads);
    }

    /**
     * @throws \Exception
     */
    public function testConvertedCounter(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->now,
        ]);

        // $contactStatusActiveYesterday
        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::LEAD,
            'contact_id' => $contact->id,
            'created_at' => $this->now,
        ]);

        // $contactStatusLeadTomorrow
        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::ACTIVE,
            'contact_id' => $contact->id,
            'created_at' => $this->tomorrow,
        ]);

        $user = factory(User::class)->create();

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
        ]);

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals(1, $reportData->converted);
    }

    /**
     * @throws \Exception
     */
    public function testTouchedCounter(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->now,
        ]);

        $user = factory(User::class)->create();

        factory(ContactNote::class)->create([
            'contact_id' => $contact->id,
        ]);

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
        ]);

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals(1, $reportData->touched);
    }

    /**
     * @throws \Exception
     */
    public function testTouchedCounterWithNoteOutOfRange(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->now,
        ]);

        $user = factory(User::class)->create();

        $outOfRangeNote = factory(Note::class)->create([
            'created_at' => $this->yesterday,
        ]);

        factory(ContactNote::class)->create([
            'contact_id' => $contact->id,
        ]);

        factory(ContactNote::class)->create([
            'contact_id' => $contact->id,
            'note_id'    => $outOfRangeNote->id,
        ]);

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
        ]);

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        self::assertEquals(1, $reportData->touched);
    }

    /**
     * @throws \Exception
     */
    public function testMeetingCounter(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->now,
        ]);

        $user = factory(User::class)->create();

        factory(ContactNote::class)->create([
            'contact_id' => $contact->id,
        ]);

        factory(ContactNote::class)->create([
            'contact_id' => $contact->id,
            'meeting_id' => null,
        ]);

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
        ]);

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        // note + note as meeting
        self::assertEquals(2, $reportData->touched);
        // note as meeting
        self::assertEquals(1, $reportData->meetings);
    }

    /**
     * @throws \Exception
     */
    public function testRevenue(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->now,
        ]);

        $user = factory(User::class)->create();

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
        ]);

        $location = factory(Location::class)->create();

        $jobs               = $this->fakeJobsWithStatus(JobStatuses::NEW, [
            'assigned_location_id' => $location->id,
        ]);
        $invoices           = [];
        $withApproveRequest = true;
        foreach ($jobs as $job) {
            $date       = $this->faker->randomElement([$this->now, $this->tomorrow]);
            $invoices[] = InvoicesTestFactory::createInvoices(
                1,
                [
                    'job_id'               => $job->id,
                    'recipient_contact_id' => $contact->id,
                    'date'                 => $date,
                    'locked_at'            => $date,
                ],
                FinancialEntityStatuses::APPROVED,
                $withApproveRequest
            )->each(function (Invoice $invoice) {
                factory(InvoiceItem::class, $this->faker->numberBetween(1, 3))->create([
                    'invoice_id' => $invoice->id,
                ]);
            })->first();
        }

        $revenue = 0;
        foreach ($invoices as $invoice) {
            $revenue += Invoice::find($invoice->id)
                ->getSubTotalAmount();
        }

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();
        self::assertTrue(Decimal::areEquals($revenue, $reportData->revenue));
    }

    /**
     * @throws \Exception
     */
    public function testRevenueLockedAtInvoice(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'created_at' => $this->now,
        ]);

        $user = factory(User::class)->create();

        factory(ManagedAccount::class)->create([
            'user_id'    => $user->id,
            'contact_id' => $contact->id,
        ]);

        $location = factory(Location::class)->create();

        $jobs               = $this->fakeJobsWithStatus(JobStatuses::NEW, [
            'assigned_location_id' => $location->id,
        ]);
        $invoices           = [];
        $withApproveRequest = true;
        foreach ($jobs as $job) {
            $date       = $this->faker->randomElement([$this->now, $this->tomorrow]);
            $invoices[] = InvoicesTestFactory::createInvoices(
                1,
                [
                    'job_id'               => $job->id,
                    'recipient_contact_id' => $contact->id,
                    'date'                 => $date,
                    'locked_at'            => null,
                ],
                FinancialEntityStatuses::APPROVED,
                $withApproveRequest
            )->each(function (Invoice $invoice) {
                factory(InvoiceItem::class, $this->faker->numberBetween(1, 3))->create([
                    'invoice_id' => $invoice->id,
                ]);
            })->first();
        }

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();
        self::assertTrue(Decimal::areEquals(0, $reportData->revenue));
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testTagsDistribution(): void
    {
        $managedContactsCount = 3;
        $contacts             = factory(Contact::class, $managedContactsCount)->create();
        $user                 = factory(User::class)->create();

        foreach ($contacts as $contact) {
            factory(ContactTag::class)->create([
                'contact_id' => $contact->id,
            ]);

            factory(ManagedAccount::class)->create([
                'user_id'    => $user->id,
                'contact_id' => $contact->id,
                'created_at' => $this->now,
            ]);
        }

        $filter            = new ContactVolumeReportFilter();
        $filter->date_from = $this->now;
        $filter->date_to   = $this->tomorrow;
        $filter->staff_id  = $user->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();

        foreach ($reportData->tags as $tagData) {
            self::assertTrue(Decimal::areEquals($tagData['percent'], 100 / 3));
        }
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testSeveralManagedContactsSeveralUsersWhenContactsWasLeadBeforeAndBecomeActiveInRange(): void
    {
        $count = 2;
        //Contact has active status by default
        $contacts = factory(Contact::class, $count)->create();
        $users    = factory(User::class, $count)->create();
        $location = factory(Location::class)->create();

        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::LEAD,
            'contact_id' => $contacts->first()->id,
            'created_at' => Carbon::now()->addMinute(1),
        ]);

        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::LEAD,
            'contact_id' => $contacts->last()->id,
            'created_at' => Carbon::now()->addMinute(1),
        ]);

        factory(LocationUser::class)->create([
            'user_id'     => $users->first()->id,
            'location_id' => $location->id,
        ]);

        factory(LocationUser::class)->create([
            'user_id'     => $users->last()->id,
            'location_id' => $location->id,
        ]);

        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::ACTIVE,
            'contact_id' => $contacts->first()->id,
            'created_at' => $this->tomorrow->copy()->addDay(),
        ]);

        factory(ContactStatus::class)->create([
            'status'     => ContactStatuses::ACTIVE,
            'contact_id' => $contacts->last()->id,
            'created_at' => $this->tomorrow->copy()->addDay(),
        ]);

        factory(ManagedAccount::class)->create([
            'user_id'    => $users->first()->id,
            'contact_id' => $contacts->first()->id,
            'created_at' => $this->tomorrow,
        ]);

        factory(ManagedAccount::class)->create([
            'user_id'    => $users->last()->id,
            'contact_id' => $contacts->last()->id,
            'created_at' => $this->tomorrow,
        ]);

        $filter              = new ContactVolumeReportFilter();
        $filter->date_from   = $this->tomorrow;
        $filter->date_to     = $this->tomorrow->copy()->addDay(2);
        $filter->location_id = $location->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();
        self::assertEquals($count, $reportData->managed);
        self::assertEquals(0, $reportData->newLeads);
        self::assertEquals(0, $reportData->touched);
        self::assertEquals($count, $reportData->converted);
        self::assertEquals(0, $reportData->revenue);
        self::assertEmpty($reportData->chart);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testSeveralManagedContactsSeveralUsersWithNotesAndTags(): void
    {
        $count = 2;
        //Contact has active status by default
        $contacts = factory(Contact::class, $count)->create();
        $users    = factory(User::class, $count)->create();
        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $users->first()->id,
            'location_id' => $location->id,
        ]);

        factory(LocationUser::class)->create([
            'user_id'     => $users->last()->id,
            'location_id' => $location->id,
        ]);

        factory(ContactTag::class)->create([
            'contact_id' => $contacts->first()->id,
        ]);

        factory(ContactTag::class)->create([
            'contact_id' => $contacts->last()->id,
        ]);

        factory(ContactNote::class)->create([
            'contact_id' => $contacts->first()->id,
        ]);

        factory(ContactNote::class)->create([
            'contact_id' => $contacts->last()->id,
        ]);

        factory(ManagedAccount::class)->create([
            'user_id'    => $users->first()->id,
            'contact_id' => $contacts->first()->id,
            'created_at' => $this->tomorrow,
        ]);

        factory(ManagedAccount::class)->create([
            'user_id'    => $users->last()->id,
            'contact_id' => $contacts->last()->id,
            'created_at' => $this->tomorrow,
        ]);

        $filter              = new ContactVolumeReportFilter();
        $filter->date_from   = $this->now;
        $filter->date_to     = $this->tomorrow;
        $filter->location_id = $location->id;

        $this->contactVolumeReportService->setFilter($filter);

        $reportData = $this->contactVolumeReportService->getReportData();
        self::assertEquals($count, $reportData->managed);
        self::assertEquals(0, $reportData->newLeads);
        self::assertEquals(2, $reportData->touched);
        self::assertEquals(0, $reportData->converted);
        self::assertEquals(0, $reportData->revenue);
        self::assertEmpty($reportData->chart);
    }
}
