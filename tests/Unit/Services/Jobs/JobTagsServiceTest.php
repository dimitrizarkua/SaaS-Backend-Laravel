<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobTagsServiceInterface;
use App\Components\Jobs\Models\JobTag;
use App\Components\Tags\Models\Tag;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobTagsServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   services
 */
class JobTagsServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobTagsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobTagsServiceInterface::class);
    }

    /**
     * @throws \Throwable
     */
    public function testAssignTag()
    {
        $job = $this->fakeJobWithStatus();
        $tag = factory(Tag::class)->create();

        $this->service->assignTag($job->id, $tag->id);

        $jobTag = JobTag::query()
            ->where([
                'job_id' => $job->id,
                'tag_id' => $tag->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->tags()->count());
        self::assertEquals($tag->id, $jobTag->tag_id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailAssignTagToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $tag = factory(Tag::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->assignTag($job->id, $tag->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAssignTagThatAssigned()
    {
        $job = $this->fakeJobWithStatus();
        $tag = factory(Tag::class)->create();

        $this->service->assignTag($job->id, $tag->id);

        self::expectExceptionMessage('This tag is already assigned to specified job.');
        self::expectException(NotAllowedException::class);
        $this->service->assignTag($job->id, $tag->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUnAssignTag()
    {
        $jobTag = factory(JobTag::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $this->service->unassignTag($jobTag->job_id, $jobTag->tag_id);

        self::expectException(ModelNotFoundException::class);
        JobTag::query()
            ->where([
                'job_id' => $jobTag->job_id,
                'tag_id' => $jobTag->tag_id,
            ])
            ->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testFailUnAssignTagFromClosedJob()
    {
        $job    = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobTag = factory(JobTag::class)->create([
            'job_id' => $job->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->unassignTag($jobTag->job_id, $jobTag->tag_id);
    }
}
