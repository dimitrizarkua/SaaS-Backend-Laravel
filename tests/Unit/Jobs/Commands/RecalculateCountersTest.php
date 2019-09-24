<?php

namespace Tests\Unit\Jobs\Commands;

use App\Components\Jobs\Enums\JobCountersCacheKeys;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Jobs\Models\JobTeam;
use App\Components\Jobs\Models\JobUser;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * Class RecalculateCountersTest
 *
 * @package Tests\Unit\Jobs
 * @group   jobs
 */
class RecalculateCountersTest extends TestCase
{
    /** @var \Illuminate\Cache\TaggedCache $cache */
    private $cache;

    /** @var JobsServiceInterface $service */
    private $service;

    private $commandName = 'jobs:recalc_counters';
    private $confirm     = 'Will recalculate counters for all users. Continue?';

    public function setUp()
    {
        parent::setUp();
        $this->cache   = taggedCache(JobCountersCacheKeys::TAG_KEY);
        $this->service = Container::getInstance()->make(JobsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testRecalcAllUsers()
    {
        $this->createJobWithUser();

        $this->artisan($this->commandName)
            ->expectsQuestion($this->confirm, 'yes')
            ->assertExitCode(0);

        $inboxCnt = $this->cache->get(JobCountersCacheKeys::INBOX_KEY);

        self::assertEquals(1, $inboxCnt);
    }

    public function testCheckInboxCounter()
    {
        $jobUser = $this->createJobWithUser();

        $this->artisan($this->commandName, ['--user' => $jobUser->user_id])
            ->assertExitCode(0);

        $inboxCnt = $this->cache->get(JobCountersCacheKeys::INBOX_KEY);

        self::assertEquals(1, $inboxCnt);
    }

    public function testCheckMineCounter()
    {
        $jobUser = $this->createJobWithUser();

        $this->artisan($this->commandName, ['--user' => $jobUser->user_id])
            ->assertExitCode(0);

        $mineKey = sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $jobUser->user_id);
        $mineRaw = $this->cache->get($mineKey);
        $mineCnt = json_decode($mineRaw, true);

        self::assertEquals(1, $mineCnt['mine']);
    }

    public function testCommandFixIndexCounter()
    {
        $this->createJobWithUser();
        $this->cache->forever(JobCountersCacheKeys::INBOX_KEY, 999);

        $this->artisan($this->commandName)
            ->expectsQuestion($this->confirm, 'yes')
            ->assertExitCode(0);

        $inboxCnt = $this->cache->get(JobCountersCacheKeys::INBOX_KEY);

        self::assertEquals(1, $inboxCnt);
    }

    public function testCommandFixMineCounter()
    {
        $jobUser = $this->createJobWithUser();

        $this->cache->forever(sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $jobUser->user_id), 999);

        $this->artisan($this->commandName)
            ->expectsQuestion($this->confirm, 'yes')
            ->assertExitCode(0);

        $mineKey = sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $jobUser->user_id);
        $mineRaw = $this->cache->get($mineKey);
        $mineCnt = json_decode($mineRaw, true);

        self::assertEquals(1, $mineCnt['mine']);
    }

    /**
     * @return mixed
     */
    private function createJobWithUser()
    {
        $jobUser = factory(JobUser::class)->create();

        $job            = Job::find($jobUser->job_id);
        $job->pinned_at = Carbon::now();
        $job->save();

        factory(JobStatus::class)->create([
            'status' => JobStatuses::NEW,
            'job_id' => $job->id,
        ]);

        return $jobUser;
    }
}
