<?php

namespace Tests\API\Reporting;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactNote;
use App\Components\Contacts\Models\ContactTag;
use App\Components\Contacts\Models\ManagedAccount;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Http\Responses\Reporting\ContactVolumeReportResponse;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\InvoicesTestFactory;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class ReportingContactsControllerTest
 *
 * @package Tests\API\Reporting
 * @group   contacts
 * @group   reporting
 */
class ReportingContactsControllerTest extends ApiTestCase
{
    use JobFaker;

    /** @var Carbon */
    private $yesterday;

    /** @var Carbon */
    private $tomorrow;

    /** @var Carbon */
    private $now;

    protected $permissions = [
        'contacts.reports.view',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->now       = Carbon::now();
        $this->yesterday = $this->now->copy()->subDay();
        $this->tomorrow  = $this->now->copy()->addDay();
    }

    public function testVolumeReport()
    {
        $managedContactsCount = $this->faker->numberBetween(1, 3);
        $contacts             = factory(Contact::class, $managedContactsCount)->create();
        $user                 = factory(User::class)->create();

        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $user->id,
            'location_id' => $location->id,
        ]);

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

        factory(ContactNote::class)->create([
            'contact_id' => $contacts->first()->id,
        ]);

        factory(ContactNote::class)->create([
            'contact_id' => $contacts->first()->id,
            'meeting_id' => null,
        ]);

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
                ],
                FinancialEntityStatuses::APPROVED,
                $withApproveRequest
            )->each(function (Invoice $invoice) {
                factory(InvoiceItem::class, $this->faker->numberBetween(1, 3))->create([
                    'invoice_id' => $invoice->id,
                ]);
            })->first();
        }

        $filter = [
            'location_id' => $location->id,
            'date_from'   => $this->now->format('Y-m-d'),
            'date_to'     => $this->tomorrow->format('Y-m-d'),
            'staff_id'    => $user->id,
        ];

        $url = action('Reporting\ReportingContactsController@volumeReport', $filter);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(ContactVolumeReportResponse::class, true)
            ->assertSeeData();
    }
}
