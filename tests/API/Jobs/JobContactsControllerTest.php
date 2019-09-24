<?php

namespace Tests\API\Jobs;

use App\Components\Contacts\Models\Contact;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\JobContactAssignment;
use App\Components\Jobs\Models\JobContactAssignmentType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class JobContactsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobContactsControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_contacts',
    ];

    public function testListContactAssignmentTypes()
    {
        $count = $this->faker->numberBetween(1, 5);

        factory(JobContactAssignmentType::class, $count)->create();

        $url = action('Jobs\JobContactsController@listAssignmentTypes');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($count, 'data');
    }

    public function testListAssignedContacts()
    {
        $job = $this->fakeJobWithStatus();

        /** @var JobContactAssignmentType $type */
        $type = factory(JobContactAssignmentType::class)->create();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobContactAssignment::class, $count)->create([
            'job_id'                 => $job->id,
            'job_assignment_type_id' => $type->id,
        ]);

        $url = action('Jobs\JobContactsController@listAssignedContacts', ['job_id' => $job->id,]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($count, 'data');
    }

    public function testAssignContactToJob()
    {
        $job = $this->fakeJobWithStatus();

        /** @var JobContactAssignmentType $type */
        $type = factory(JobContactAssignmentType::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();

        $url  = action('Jobs\JobContactsController@assignContact', [
            'job_id'     => $job->id,
            'contact_id' => $contact->id,
        ]);
        $data = ['assignment_type_id' => $type->id,];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount(1);

        /** @var \App\Components\Jobs\Models\JobContactAssignment $assignment */
        $assignment = JobContactAssignment::query()->where([
            'job_id'                 => $job->id,
            'job_assignment_type_id' => $type->id,
            'assignee_contact_id'    => $contact->id,
        ])->firstOrFail();

        self::assertEquals($this->user->id, $assignment->assigner_id);
    }

    public function testFailAssignContactToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var JobContactAssignmentType $type */
        $type = factory(JobContactAssignmentType::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();

        $url = action('Jobs\JobContactsController@assignContact', [
            'job_id'     => $job->id,
            'contact_id' => $contact->id,
        ]);

        $data = ['assignment_type_id' => $type->id,];
        $this->postJson($url, $data)->assertStatus(405);
    }

    public function testFailToAssignWhenAlreadyAssignedWithSameType()
    {
        /** @var JobContactAssignment $assignment */
        $assignment = factory(JobContactAssignment::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $url  = action('Jobs\JobContactsController@assignContact', [
            'job_id'     => $assignment->job_id,
            'contact_id' => $assignment->assignee_contact_id,
        ]);
        $data = [
            'assignment_type_id' => $assignment->job_assignment_type_id,
            'invoice_to'         => $this->faker->boolean,
        ];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(405)
            ->assertSee('This contact already assigned');
    }

    public function testFailToAssignWhenAnotherContactAlreadyAssignedWithUniqueType()
    {
        /** @var JobContactAssignmentType $type */
        $type = factory(JobContactAssignmentType::class)->create([
            'is_unique' => true,
        ]);
        /** @var JobContactAssignment $assignment */
        $assignment = factory(JobContactAssignment::class)->create([
            'job_id'                 => $this->fakeJobWithStatus()->id,
            'job_assignment_type_id' => $type->id,
        ]);
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();

        $url  = action('Jobs\JobContactsController@assignContact', [
            'job_id'     => $assignment->job_id,
            'contact_id' => $contact->id,
        ]);
        $data = [
            'assignment_type_id' => $type->id,
            'invoice_to'         => $this->faker->boolean,
        ];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(405)
            ->assertSee('Another contact has been already assigned');
    }

    public function testUpdateContactAssignment()
    {
        /** @var JobContactAssignmentType $type */
        $type = factory(JobContactAssignmentType::class)->create();
        /** @var JobContactAssignment $assignment */
        $assignment = factory(JobContactAssignment::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $url  = action('Jobs\JobContactsController@updateAssignment', [
            'job_id'     => $assignment->job_id,
            'contact_id' => $assignment->assignee_contact_id,
        ]);
        $data = [
            'assignment_type_id'     => $assignment->job_assignment_type_id,
            'new_assignment_type_id' => $type->id,
        ];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount(1);

        /** @var \App\Components\Jobs\Models\JobContactAssignment $updatedAssignment */
        $updatedAssignment = JobContactAssignment::query()->where([
            'job_id'                 => $assignment->job_id,
            'job_assignment_type_id' => $type->id,
            'assignee_contact_id'    => $assignment->assignee_contact_id,
        ])->firstOrFail();

        self::assertEquals($this->user->id, $updatedAssignment->assigner_id);

        self::expectException(ModelNotFoundException::class);

        JobContactAssignment::query()->where([
            'job_id'                 => $assignment->job_id,
            'job_assignment_type_id' => $assignment->job_assignment_type_id,
            'assignee_contact_id'    => $assignment->assignee_contact_id,
        ])->firstOrFail();
    }

    public function testFailToUpdateWhenAlreadyAssignedWithRequestedType()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        /** @var JobContactAssignmentType $type1 */
        $type1 = factory(JobContactAssignmentType::class)->create();
        /** @var JobContactAssignmentType $type2 */
        $type2 = factory(JobContactAssignmentType::class)->create();

        // Existing assignment
        factory(JobContactAssignment::class)->create([
            'job_id'                 => $job->id,
            'job_assignment_type_id' => $type1->id,
            'assignee_contact_id'    => $contact->id,
        ]);

        // Conflicting assignment
        factory(JobContactAssignment::class)->create([
            'job_id'                 => $job->id,
            'job_assignment_type_id' => $type2->id,
            'assignee_contact_id'    => $contact->id,
        ]);

        $url  = action('Jobs\JobContactsController@updateAssignment', [
            'job_id'     => $job->id,
            'contact_id' => $contact->id,
        ]);
        $data = [
            'assignment_type_id'     => $type1->id,
            'new_assignment_type_id' => $type2->id,
        ];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);

        $response->assertStatus(405)
            ->assertSee('This contact already assigned as');
    }

    public function testFailToUpdateWhenAnotherContactAlreadyAssignedWithUniqueType()
    {
        $job = $this->fakeJobWithStatus();

        /** @var JobContactAssignmentType $regularType */
        $regularType = factory(JobContactAssignmentType::class)->create();
        /** @var JobContactAssignmentType $specialType */
        $specialType = factory(JobContactAssignmentType::class)->create([
            'is_unique' => true,
        ]);

        /** @var JobContactAssignment $existingAssignment */
        $existingAssignment = factory(JobContactAssignment::class)->create([
            'job_id'                 => $job->id,
            'job_assignment_type_id' => $regularType->id,
        ]);

        // Conflicting assignment
        factory(JobContactAssignment::class)->create([
            'job_id'                 => $job->id,
            'job_assignment_type_id' => $specialType->id,
        ]);

        $url  = action('Jobs\JobContactsController@updateAssignment', [
            'job_id'     => $job->id,
            'contact_id' => $existingAssignment->assignee_contact_id,
        ]);
        $data = [
            'assignment_type_id'     => $regularType->id,
            'new_assignment_type_id' => $specialType->id,
        ];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);

        $response->assertStatus(405)
            ->assertSee('Another contact has been already assigned as');
    }

    public function testUnassignContactFromJob()
    {
        $job     = $this->fakeJobWithStatus();
        $contact = factory(Contact::class)->create();
        $count   = $this->faker->numberBetween(1, 3);

        $assignments = factory(JobContactAssignment::class, $count)->create([
            'assignee_contact_id' => $contact->id,
            'job_id'              => $job->id,
        ]);

        /** @var JobContactAssignment $assignment */
        $assignment = $this->faker->randomElement($assignments);

        $url = action('Jobs\JobContactsController@unassignContact', [
            'job_id'     => $job->id,
            'contact_id' => $contact->id,
        ]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url, [
            'assignment_type_id' => $assignment->job_assignment_type_id,
        ]);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount(count($assignments) - 1);

        self::expectException(ModelNotFoundException::class);

        JobContactAssignment::query()->where([
            'job_id'                 => $assignment->job_id,
            'job_assignment_type_id' => $assignment->job_assignment_type_id,
            'assignee_contact_id'    => $assignment->assignee_contact_id,
        ])->firstOrFail();
    }

    public function testFailUnassignContactFromClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var JobContactAssignment $assignment */
        $assignment = factory(JobContactAssignment::class)->create([
            'job_id' => $job->id,
        ]);

        $url  = action('Jobs\JobContactsController@unassignContact', [
            'job_id'     => $assignment->job_id,
            'contact_id' => $assignment->assignee_contact_id,
        ]);
        $data = ['assignment_type_id' => $assignment->job_assignment_type_id,];

        /** @var \Tests\API\TestResponse $response */
        $this->deleteJson($url, $data)->assertStatus(405);
    }
}
