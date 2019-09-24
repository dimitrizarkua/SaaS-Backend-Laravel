<?php

namespace Tests\Unit\Jobs\Models;

use App\Components\Contacts\Models\Contact;
use App\Components\Jobs\Models\JobContactAssignment;
use Tests\API\Jobs\JobTestCase;

/**
 * Class JobModelTest
 *
 * @package Tests\Unit\Jobs\Models
 * @group   jobs
 */
class JobModelTest extends JobTestCase
{
    /**
     * @throws \Throwable
     */
    public function testInvoiceToLocationContact()
    {
        $job = $this->fakeJobWithStatus();

        $notInvoiceToContacts = factory(Contact::class, $this->faker->numberBetween(1, 5))->create();

        foreach ($notInvoiceToContacts as $notInvoiceToContact) {
            factory(JobContactAssignment::class)->create([
                'job_id'              => $job->id,
                'assignee_contact_id' => $notInvoiceToContact->id,
            ]);
        }

        $invoiceToContact = factory(Contact::class)->create();

        // Create two same job contact assignments with different job_assignment_type_id.
        factory(JobContactAssignment::class)->create([
            'job_id'              => $job->id,
            'assignee_contact_id' => $invoiceToContact->id,
            'invoice_to'          => true,
        ]);

        factory(JobContactAssignment::class)->create([
            'job_id'              => $job->id,
            'assignee_contact_id' => $invoiceToContact->id,
            'invoice_to'          => true,
        ]);

        $expectedInvoiceToContact = $job->toSearchableArray()['data']['invoice_to_contact'];

        self::assertEquals($invoiceToContact->id, $expectedInvoiceToContact['contact_id']);
        self::assertEquals($invoiceToContact->getContactName(), $expectedInvoiceToContact['contact_name']);
    }
}
