<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobNote;
use App\Components\Notes\Models\Note;

/**
 * Class JobNotesControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobNotesControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_notes',
    ];

    public function testListNotes()
    {
        $job = $this->fakeJobWithStatus();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobNote::class, $count)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobNotesController@listNotes', ['job_id' => $job->id,]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testAddNoteToJob()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);

        $url = action('Jobs\JobNotesController@addNote', [
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));

        JobNote::query()->where([
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ])->firstOrFail();
    }

    public function testFailAddNoteToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);

        $url = action('Jobs\JobNotesController@addNote', [
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testAddNoteToJobAndChangeStatus()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);

        $status = $this->faker->randomElement(JobStatuses::values());

        $url = action('Jobs\JobNotesController@addNote', [
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ]);

        $this->postJson($url, ['new_status' => $status])
            ->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));

        /** @var JobNote $jobNote */
        $jobNote = JobNote::query()->where([
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ])->firstOrFail();

        self::assertNotNull($jobNote->job_status_id);

        /** @var Job $job */
        $job = Job::findOrFail($job->id);

        self::assertEquals($status, $job->getCurrentStatus());
    }

    public function testFailToAddNoteOwnedByOtherUser()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Note $note */
        $note = factory(Note::class)->create();

        $url = action('Jobs\JobNotesController@addNote', [
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(403)
            ->assertSee('You are not authorized to perform this action.');
    }

    public function testFailToAddWhenAlreadyAdded()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);

        factory(JobNote::class)->create([
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ]);

        $url = action('Jobs\JobNotesController@addNote', [
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(405)
            ->assertSee('This note is already added to specified job.');
    }

    public function testRemoveNoteFromJob()
    {
        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);

        /** @var JobNote $jobNote */
        $jobNote = factory(JobNote::class)->create([
            'job_id'  => $this->fakeJobWithStatus()->id,
            'note_id' => $note->id,
        ]);

        $url = action('Jobs\JobNotesController@deleteNote', [
            'job_id'  => $jobNote->job_id,
            'note_id' => $jobNote->note_id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        /** @var JobNote $jobNote */
        $jobNote = JobNote::query()->where([
            'job_id'  => $jobNote->job_id,
            'note_id' => $jobNote->note_id,
        ])->first();

        self::assertNull($jobNote);
    }

    public function testFailRemoveNoteFromClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);

        /** @var JobNote $jobNote */
        $jobNote = factory(JobNote::class)->create([
            'job_id'  => $job->id,
            'note_id' => $note->id,
        ]);

        $url = action('Jobs\JobNotesController@deleteNote', [
            'job_id'  => $jobNote->job_id,
            'note_id' => $jobNote->note_id,
        ]);

        $this->deleteJson($url)->assertStatus(405);
    }
}
