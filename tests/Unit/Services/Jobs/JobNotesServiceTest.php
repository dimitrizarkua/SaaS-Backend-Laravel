<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobNote;
use App\Components\Notes\Models\Note;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobNotesServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   services
 */
class JobNotesServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobNotesServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobNotesServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \Throwable
     */
    public function testAddNote()
    {
        $job  = $this->fakeJobWithStatus();
        $note = factory(Note::class)->create();

        $this->service->addNote($job->id, $note->id);

        JobNote::query()
            ->where([
                'note_id' => $note->id,
                'job_id'  => $job->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->notes()->count());
    }

    /**
     * @throws \Throwable
     */
    public function testFailAddNoteToClosedJob()
    {
        $job  = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $note = factory(Note::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->addNote($job->id, $note->id);
    }

    /**
     * @throws \Throwable
     */
    public function testAddNoteWithStatus()
    {
        /** @var Job $job */
        $job                = $this->fakeJobWithStatus();
        $note               = factory(Note::class)->create();
        $expectedStatusName = JobStatuses::IN_PROGRESS;

        $this->service->addNote($job->id, $note->id, $expectedStatusName);

        $latestStatus = $job->latestStatus();
        JobNote::query()
            ->where([
                'note_id'       => $note->id,
                'job_id'        => $job->id,
                'job_status_id' => $latestStatus->value('id'),
            ])
            ->firstOrFail();

        self::assertEquals($expectedStatusName, $latestStatus->value('status'));
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAddNoteWithInvalidStatus()
    {
        $job                = $this->fakeJobWithStatus();
        $note               = factory(Note::class)->create();
        $expectedStatusName = 'INVALID';

        self::expectException(NotAllowedException::class);
        self::expectExceptionMessage('This note is already added to specified job.');
        $this->service->addNote($job->id, $note->id, $expectedStatusName);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAddNoteWasAlreadyAttached()
    {
        $job  = $this->fakeJobWithStatus();
        $note = factory(Note::class)->create();

        $this->service->addNote($job->id, $note->id);

        self::expectExceptionMessage('This note is already added to specified job.');
        self::expectException(NotAllowedException::class);
        $this->service->addNote($job->id, $note->id);
    }

    /**
     * @throws \Throwable
     */
    public function testRemoveNote()
    {
        $jobNote = factory(JobNote::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $this->service->removeNote($jobNote->job_id, $jobNote->note_id);

        $jobNote = JobNote::query()
            ->where([
                'note_id' => $jobNote->note_id,
                'job_id'  => $jobNote->job_id,
            ])
            ->first();

        self::assertNull($jobNote);
    }

    /**
     * @throws \Throwable
     */
    public function testFailRemoveNoteFromClosedJob()
    {
        $job     = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobNote = factory(JobNote::class)->create([
            'job_id' => $job->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->removeNote($jobNote->job_id, $jobNote->note_id);
    }
}
