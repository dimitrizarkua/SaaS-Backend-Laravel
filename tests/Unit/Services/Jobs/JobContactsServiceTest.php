<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactPersonProfile;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobContactsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobContactAssignment;
use App\Components\Jobs\Models\JobContactAssignmentType;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobContactsServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   services
 */
class JobContactsServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobContactsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobContactsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * Trigger will set invoice_to = true by default for any first contact.
     * Trigger will set invoice_to = false for all and set to true for new assigned contact with invoice_to = true
     *
     * @see migrations/sql/update_invoice_to_field_for_job_contact_assignments.sql for details
     *
     * @throws \Throwable
     */
    public function testAssignContactByDefault()
    {
        $job                      = $this->fakeJobWithStatus();
        $contact                  = factory(Contact::class)->create();
        $jobContactAssignmentType = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id
        );

        $jobAssignment = JobContactAssignment::query()
            ->where([
                'assignee_contact_id'    => $contact->id,
                'job_assignment_type_id' => $jobContactAssignmentType->id,
                'job_id'                 => $job->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->assignedContacts()->count());
        self::assertNull($jobAssignment->assigner_id);
        self::assertTrue($jobAssignment->invoice_to);
    }

    /**
     * @throws \Throwable
     */
    public function testFailAssignContactToClosedJob()
    {
        $job                      = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $contact                  = factory(Contact::class)->create();
        $assigner                 = factory(User::class)->create();
        $jobContactAssignmentType = factory(JobContactAssignmentType::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id,
            false,
            $assigner->id
        );
    }

    /**
     * @throws \Throwable
     */
    public function testAssignTwoContacts()
    {
        $job                      = $this->fakeJobWithStatus();
        $contact                  = factory(Contact::class)->create();
        $contact2                 = factory(Contact::class)->create();
        $jobContactAssignmentType = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id,
            true
        );

        $this->service->assignContact(
            $job->id,
            $contact2->id,
            $jobContactAssignmentType->id,
            false
        );

        self::assertEquals(2, $job->assignedContacts()->count());
        self::assertEquals(1, $job->assignedContacts()->where('invoice_to', true)->count());
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAssignThatAlreadyAssigned()
    {
        $job                      = $this->fakeJobWithStatus();
        $contact                  = factory(Contact::class)->create();
        $assigner                 = factory(User::class)->create();
        $jobContactAssignmentType = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id,
            false,
            $assigner->id
        );

        self::expectExceptionMessage(
            sprintf(
                'This contact already assigned as %s to specified job.',
                $jobContactAssignmentType->name
            )
        );

        self::expectException(NotAllowedException::class);
        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id,
            false,
            $assigner->id
        );
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAssignToSpecialJobContactAssignmentType()
    {
        $job                      = $this->fakeJobWithStatus();
        $contact                  = factory(Contact::class)->create();
        $contact2                 = factory(Contact::class)->create();
        $jobContactAssignmentType = factory(JobContactAssignmentType::class)->create([
            'is_unique' => true,
        ]);

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id
        );

        self::expectException(NotAllowedException::class);
        $this->service->assignContact(
            $job->id,
            $contact2->id,
            $jobContactAssignmentType->id
        );
    }

    /**
     * @throws \Throwable
     */
    public function testAssignWithAssigner()
    {
        $job                      = $this->fakeJobWithStatus();
        $contact                  = factory(Contact::class)->create();
        $assigner                 = factory(User::class)->create();
        $jobContactAssignmentType = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id,
            false,
            $assigner->id
        );

        $jobAssignment = JobContactAssignment::query()
            ->where([
                'assignee_contact_id'    => $contact->id,
                'job_assignment_type_id' => $jobContactAssignmentType->id,
                'job_id'                 => $job->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->assignedContacts()->count());
        self::assertEquals($assigner->id, $jobAssignment->assigner_id);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateContactAssignment()
    {
        $job                       = $this->fakeJobWithStatus();
        $contact                   = factory(Contact::class)->create();
        $assigner                  = factory(User::class)->create();
        $assigner2                 = factory(User::class)->create();
        $jobContactAssignmentType  = factory(JobContactAssignmentType::class)->create();
        $jobContactAssignmentType2 = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id,
            false,
            $assigner->id
        );
        $this->service->updateContactAssignment(
            $job->id,
            $contact->id,
            $jobContactAssignmentType->id,
            false,
            $assigner2->id,
            $jobContactAssignmentType2->id
        );

        $jobAssignment = JobContactAssignment::query()
            ->where([
                'assignee_contact_id'    => $contact->id,
                'job_assignment_type_id' => $jobContactAssignmentType2->id,
                'job_id'                 => $job->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->assignedContacts()->count());
        self::assertEquals($assigner2->id, $jobAssignment->assigner_id);
        self::assertTrue($jobAssignment->invoice_to);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateAssignmentsThatAlreadyAssigned()
    {
        $job               = $this->fakeJobWithStatus();
        $contact           = factory(Contact::class)->create();
        $jobAssignmentType = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobAssignmentType->id,
            false
        );

        self::expectExceptionMessage(
            sprintf('This contact already assigned as %s to specified job.', $jobAssignmentType->name)
        );

        self::expectException(NotAllowedException::class);
        $this->service->updateContactAssignment(
            $job->id,
            $contact->id,
            $jobAssignmentType->id,
            false,
            null,
            $jobAssignmentType->id
        );
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateAssignmentsToSpecialJobContactAssignmentType()
    {
        $job               = $this->fakeJobWithStatus();
        $contact           = factory(Contact::class)->create();
        $contact2          = factory(Contact::class)->create();
        $customerType      = factory(JobContactAssignmentType::class)->create([
            'is_unique' => true,
        ]);
        $jobAssignmentType = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $customerType->id,
            false
        );

        $this->service->assignContact(
            $job->id,
            $contact2->id,
            $jobAssignmentType->id
        );

        self::expectExceptionMessage(
            sprintf('Another contact has been already assigned as %s to specified job.', $customerType->name)
        );

        self::expectException(NotAllowedException::class);
        $this->service->updateContactAssignment(
            $job->id,
            $contact2->id,
            $jobAssignmentType->id,
            false,
            null,
            $customerType->id
        );
    }

    /**
     * @throws \Throwable
     */
    public function testUnassignContact()
    {
        $jobContact = factory(JobContactAssignment::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $this->service->unassignContact(
            $jobContact->job_id,
            $jobContact->assignee_contact_id,
            $jobContact->job_assignment_type_id
        );

        self::expectException(ModelNotFoundException::class);
        JobContactAssignment::query()
            ->where([
                'job_id'                 => $jobContact->job_id,
                'assignee_contact_id'    => $jobContact->assignee_contact_id,
                'job_assignment_type_id' => $jobContact->job_assignment_type_id,
            ])
            ->firstOrFail();
    }

    public function testFailToUnassignContactFromNonExistingJob()
    {
        self::expectException(ModelNotFoundException::class);
        $this->service->unassignContact(0, 0, 0);
    }

    /**
     * @throws \Throwable
     */
    public function testFailUnassignContactFromClosedJob()
    {
        $job        = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobContact = factory(JobContactAssignment::class)->create([
            'job_id' => $job->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->unassignContact(
            $jobContact->job_id,
            $jobContact->assignee_contact_id,
            $jobContact->job_assignment_type_id
        );
    }

    /**
     * @throws \Throwable
     */
    public function testGetAssignedContactsByJobId()
    {
        $job               = $this->fakeJobWithStatus();
        $contact           = factory(Contact::class)->create();
        $jobAssignmentType = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobAssignmentType->id
        );

        $assignedContacts = $this->service->getAssignedContacts($job->id);
        self::assertEquals(1, count($assignedContacts));
        self::assertEquals(
            $jobAssignmentType->id,
            $assignedContacts[0]->pivot->job_assignment_type_id
        );
    }

    /**
     * @throws \Throwable
     */
    public function testGetAssignedContactsByJobIdAndTypeId()
    {
        $job                = $this->fakeJobWithStatus();
        $contact            = factory(Contact::class)->create();
        $jobAssignmentType  = factory(JobContactAssignmentType::class)->create();
        $jobAssignmentType2 = factory(JobContactAssignmentType::class)->create();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobAssignmentType->id
        );
        $this->service->assignContact(
            $job->id,
            $contact->id,
            $jobAssignmentType2->id
        );

        $assignedContacts       = $this->service->getAssignedContacts($job->id);
        $assignedContactsByType = $this->service->getAssignedContacts($job->id, $jobAssignmentType2->id);

        self::assertEquals(2, count($assignedContacts));
        self::assertEquals(1, count($assignedContactsByType));
    }

    /**
     * @throws \Throwable
     */
    public function testGetAssignedContactsWithPerson()
    {
        $jobContactAssignment = factory(JobContactAssignment::class)->create([
            'job_id'              => $this->fakeJobWithStatus()->id,
            'assignee_contact_id' => factory(Contact::class)->create([
                'contact_type' => ContactTypes::PERSON,
            ]),
        ]);

        $assignedContacts = $this->service->getAssignedContacts($jobContactAssignment->job_id);

        self::assertNotEmpty($assignedContacts);
        self::assertNotEmpty($assignedContacts[0]->person);
        self::assertNotEmpty($assignedContacts[0]->assignmentTypes);
    }
}
