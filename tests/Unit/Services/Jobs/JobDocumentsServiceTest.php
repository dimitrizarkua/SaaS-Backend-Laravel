<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Documents\Models\Document;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobDocumentsServiceInterface;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobDocumentsServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   services
 */
class JobDocumentsServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobDocumentsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobDocumentsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \Throwable
     */
    public function testAttachDocument()
    {
        $job      = $this->fakeJobWithStatus();
        $document = factory(Document::class)->create();

        $this->service->attachDocument($job->id, $document->id);

        self::assertDatabaseHas('job_documents', [
            'job_id'      => $job->id,
            'document_id' => $document->id,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function testFailAttachDocumentToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $document = factory(Document::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');
        $this->service->attachDocument($job->id, $document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAttachDocumentWasAlreadyAttached()
    {
        $job      = $this->fakeJobWithStatus();
        $document = factory(Document::class)->create();

        $this->service->attachDocument($job->id, $document->id);

        self::expectExceptionMessage('This document is already attached to specified job.');
        self::expectException(NotAllowedException::class);
        $this->service->attachDocument($job->id, $document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testDetachDocument()
    {
        $job      = $this->fakeJobWithStatus();
        $document = factory(Document::class)->create();

        $this->service->attachDocument($job->id, $document->id);
        $this->service->detachDocument($job->id, $document->id);

        self::assertDatabaseMissing('job_documents', [
            'job_id'      => $job->id,
            'document_id' => $document->id,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function testFailDetachDocumentToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $document = factory(Document::class)->create();
        $job->documents()->attach($document, [
            'type' => $this->faker->word
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');
        $this->service->detachDocument($job->id, $document->id);
    }
}
