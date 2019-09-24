<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Messages\Models\Message;
use App\Core\Utils\Parser;
use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * Class JobsServiceMatchingTest
 *
 * @package Tests\Unit\Services\Jobs
 */
class JobsServiceMatchingTest extends TestCase
{
    /** @var \App\Components\Jobs\Interfaces\JobMessagesServiceInterface */
    private $service;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()
            ->make(JobMessagesServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testMatchMessageToJobByIdInSubject()
    {
        /** @var Job $job */
        $job = factory(Job::class)->create();

        $subject = $this->faker->regexify(Parser::STEAMATIC_JOB_ID_VARIATIONS_REGEX);
        $subject = preg_replace('/\#?\s*\d+/', $job->id, $subject);

        /** @var Message $message */
        $message = factory(Message::class)->create(['subject' => $subject]);

        $matchingJob = $this->service->matchMessageToJob($message->id);
        self::assertEquals($job->id, $matchingJob->id);
    }

    public function testMatchMessageToJobByClaimNumberInSubject()
    {
        $subject     = $this->faker->regexify(Parser::CLAIM_NUMBER_VARIATIONS_REGEX);
        $claimNumber = Parser::parseClaimNumber($subject);

        /** @var Job $job */
        $job = factory(Job::class)->create(['claim_number' => $claimNumber]);

        /** @var Message $message */
        $message = factory(Message::class)->create(['subject' => $subject]);

        $matchingJob = $this->service->matchMessageToJob($message->id);
        self::assertEquals($job->id, $matchingJob->id);
    }

    public function testMatchMessageToJobByIdInBody()
    {
        /** @var Job $job */
        $job = factory(Job::class)->create();

        $clue = $this->faker->regexify(Parser::STEAMATIC_JOB_ID_VARIATIONS_REGEX);
        $clue = preg_replace('/\#?\s*\d+/', $job->id, $clue);

        $messageBody = $this->faker->paragraphs(3, true);

        preg_match_all('/\w+(?:-\w+)*/m', $messageBody, $matches);
        $match = $matches[0][$this->faker->numberBetween(0, count($matches[0]) - 1)];

        $messageBody = preg_replace('/' . $match . '/', $clue, $messageBody, 1);

        /** @var Message $message */
        $message = factory(Message::class)->create(['message_body' => $messageBody]);

        $matchingJob = $this->service->matchMessageToJob($message->id);
        self::assertEquals($job->id, $matchingJob->id);
    }

    public function testMatchMessageToJobByClaimNumberInBody()
    {
        $clue        = $this->faker->regexify(Parser::CLAIM_NUMBER_VARIATIONS_REGEX);
        $claimNumber = Parser::parseClaimNumber($clue);

        /** @var Job $job */
        $job = factory(Job::class)->create(['claim_number' => $claimNumber]);

        $messageBody = $this->faker->paragraphs(3, true);

        preg_match_all('/\w+(?:-\w+)*/m', $messageBody, $matches);
        $match = $matches[0][$this->faker->numberBetween(0, count($matches[0]) - 1)];

        $messageBody = preg_replace('/\b' . $match . '\b/', $clue, $messageBody, 1);

        /** @var Message $message */
        $message = factory(Message::class)->create(['message_body' => $messageBody]);

        $matchingJob = $this->service->matchMessageToJob($message->id);

        self::assertEquals($job->id, $matchingJob->id);
    }
}
