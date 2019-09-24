<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTag;
use App\Components\Tags\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class JobTagsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobTagsControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_tags',
    ];

    public function testListJobTags()
    {
        $job = $this->fakeJobWithStatus();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobTag::class, $count)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobTagsController@listJobTags', ['job_id' => $job->id,]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testTagJob()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        $url = action('Jobs\JobTagsController@tagJob', [
            'job_id' => $job->id,
            'tag_id' => $tag->id,
        ]);
        $this->postJson($url)->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->eq($reloaded->touched_at));

        JobTag::query()->where([
            'job_id' => $job->id,
            'tag_id' => $tag->id,
        ])->firstOrFail();
    }

    public function testNotAllowedResponseWhenAlreadyTagged()
    {
        $job = $this->fakeJobWithStatus();

        /** @var JobTag $jobTag */
        $jobTag = factory(JobTag::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobTagsController@tagJob', [
            'job_id' => $job->id,
            'tag_id' => $jobTag->tag_id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testFailTagClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var JobTag $jobTag */
        $jobTag = factory(JobTag::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobTagsController@tagJob', [
            'job_id' => $job->id,
            'tag_id' => $jobTag->tag_id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testUntagJob()
    {
        /** @var JobTag $jobTag */
        $jobTag = factory(JobTag::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $url = action('Jobs\JobTagsController@untagJob', [
            'job_id' => $jobTag->job_id,
            'tag_id' => $jobTag->tag_id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        JobTag::query()->where([
            'job_id' => $jobTag->job_id,
            'tag_id' => $jobTag->tag_id,
        ])->firstOrFail();
    }

    public function testFailUntagClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var JobTag $jobTag */
        $jobTag = factory(JobTag::class)->create([
            'job_id' => $job->id,
        ]);

        $url = action('Jobs\JobTagsController@untagJob', [
            'job_id' => $jobTag->job_id,
            'tag_id' => $jobTag->tag_id,
        ]);

        $this->deleteJson($url)->assertStatus(405);
    }
}
