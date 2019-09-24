<?php

namespace Tests\Unit\LaravelJobs\Jobs;

use App\Components\Addresses\Helpers\CountryHelper;
use App\Components\Addresses\Helpers\StatesHelper;
use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactPersonProfile;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Interfaces\JobContactsServiceInterface;
use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobContactAssignmentType;
use App\Components\Jobs\Models\JobService;
use App\Components\Jobs\Models\VO\JobCreationData;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationSuburb;
use App\Components\Messages\Enums\SpecialJobContactAssignmentTypes;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Models\Message;
use App\Core\Utils\Parser;
use App\Jobs\Jobs\FindOrCreateOrActivateJobForIncomingMessage;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Components\Addresses\Interfaces\AddressServiceInterface;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class FindOrCreateOrActivateJobForIncomingMessageTest
 *
 * @package Tests\Unit\LaravelJobs\Jobs
 *
 * @group   jobs
 */
class FindOrCreateOrActivateJobForIncomingMessageTest extends TestCase
{
    use JobFaker;

    /**
     * @var MessagingServiceInterface
     */
    private $messagingService;

    /**
     * @var \Mockery\MockInterface|JobsServiceInterface
     */
    private $service;

    /**
     * @var \Mockery\MockInterface|JobMessagesServiceInterface
     */
    private $jobMessagesService;

    /**
     * @var \Mockery\MockInterface|JobNotesServiceInterface
     */
    private $jobNotesService;

    /**
     * @var \Mockery\MockInterface|JobContactsServiceInterface
     */
    private $jobContactsService;


    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->messagingService = Container::getInstance()
            ->make(MessagingServiceInterface::class);

        $this->service            = \Mockery::mock(JobsServiceInterface::class);
        $this->jobMessagesService = \Mockery::mock(JobMessagesServiceInterface::class);
        $this->jobNotesService    = \Mockery::mock(JobNotesServiceInterface::class);
        $this->jobContactsService = \Mockery::mock(JobContactsServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        unset($this->messagingService, $this->service);

        parent::tearDown();
    }

    /**
     * @throws \Throwable
     */
    public function testSuccessfulHandlingWhenMatchingJobFound()
    {
        /** @var Message $message */
        $message = factory(Message::class)->create();

        /** @var Job $job */
        $job = $this->fakeJobWithStatus(JobStatuses::NEW);

        $this->jobMessagesService
            ->shouldReceive('matchMessageToJob')
            ->once()
            ->andReturn($job);

        $this->jobMessagesService
            ->shouldReceive('hasMessage')
            ->once()
            ->andReturnFalse();

        $this->jobMessagesService
            ->shouldReceive('attachMessage')
            ->once()
            ->with($job->id, $message->id)
            ->andReturnUsing(function ($jobId, $messageId) use ($job, $message) {
                return $jobId === $job->id && $messageId === $message->id;
            });

        $laravelJob = new FindOrCreateOrActivateJobForIncomingMessage($message->id);

        $laravelJob->handle(
            $this->messagingService,
            $this->service,
            $this->jobMessagesService,
            $this->jobContactsService,
            $this->jobNotesService
        );
    }

    /**
     * @throws \Throwable
     */
    public function testSuccessfulHandlingWhenMatchingJobFoundAndAlreadyAttached()
    {
        /** @var Message $message */
        $message = factory(Message::class)->create();

        /** @var Job $job */
        $job = $this->fakeJobWithStatus(JobStatuses::NEW);

        $this->jobMessagesService
            ->shouldReceive('matchMessageToJob')
            ->once()
            ->andReturn($job);

        $this->jobMessagesService
            ->shouldReceive('hasMessage')
            ->once()
            ->andReturnTrue();

        $this->jobMessagesService
            ->shouldNotReceive('attachMessage');

        $laravelJob = new FindOrCreateOrActivateJobForIncomingMessage($message->id);

        $laravelJob->handle(
            $this->messagingService,
            $this->service,
            $this->jobMessagesService,
            $this->jobContactsService,
            $this->jobNotesService
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @throws \Throwable
     */
    public function testSuccessfulHandlingWhenNoMatchingJobFound()
    {
        $claimNumber = $this->faker->regexify();

        /** @var Message $message */
        $message = factory(Message::class)->create();

        /** @var Job $job */
        $job = factory(Job::class)->create(['claim_number' => $claimNumber]);

        $this->jobMessagesService
            ->shouldReceive('matchMessageToJob')
            ->once()
            ->andReturnNull();

        $parserMock = \Mockery::mock('alias:' . Parser::class);
        $parserMock->shouldReceive('parseClaimNumber')
            ->between(1, 2)
            ->with(\Mockery::anyOf($message->subject, $message->message_body))
            ->andReturnValues($this->faker->shuffleArray([null, $claimNumber]));

        $parserMock->shouldReceive('parseJobServiceType')
            ->once()
            ->with($message->message_body)
            ->andReturnNull();

        $parserMock->shouldReceive('parseAddress')
            ->once()
            ->with($message->message_body)
            ->andReturnNull();

        $parserMock->shouldReceive('parseCustomer')
            ->once()
            ->with($message->message_body)
            ->andReturnNull();

        $this->service
            ->shouldReceive('createJob')
            ->once()
            ->withArgs(function (JobCreationData $data) use ($claimNumber) {
                return $data->getClaimNumber() === $claimNumber;
            })
            ->andReturn($job);

        $this->jobMessagesService
            ->shouldReceive('attachMessage')
            ->once()
            ->withArgs(function ($jobId, $messageId) use ($job, $message) {
                return $jobId === $job->id && $messageId === $message->id;
            });

        $laravelJob = new FindOrCreateOrActivateJobForIncomingMessage($message->id);

        $laravelJob->handle(
            $this->messagingService,
            $this->service,
            $this->jobMessagesService,
            $this->jobContactsService,
            $this->jobNotesService
        );
    }

    /**
     * Selects random words from original string and replaces them with one of the given strings.
     *
     * @param string $originalString  Original string.
     * @param array  $stringsToInject Array with strings to inject.
     *
     * @return string Modified string.
     */
    private function injectStrings(string $originalString, array $stringsToInject): string
    {
        $getNextMatchIndexForReplace = function (array &$matches, array &$usedMatches) {
            $index = $this->faker->numberBetween(0, count($matches[0]) - 1);
            while (in_array($index, $usedMatches)) {
                $index = $this->faker->numberBetween(0, count($matches[0]) - 1);
            }

            return $index;
        };

        preg_match_all('/\b\w+\b/m', $originalString, $matches);

        $usedMatches = [];

        for ($i = 0; $i < count($stringsToInject); $i++) {
            $usedMatches[] = $index = $getNextMatchIndexForReplace($matches, $usedMatches);
            $wordToReplace = $matches[0][$index];

            $originalString = preg_replace('/' . $wordToReplace . '/', $stringsToInject[$i], $originalString, 1);
        }

        return $originalString;
    }

    /**
     * @throws \Throwable
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSuccessfulHandlingWhenNoMatchingJobFoundAndMessageHasAdditionalInfo()
    {
        // Preparing search clues

        /** @var JobService $serviceType */
        $serviceType = factory(JobService::class)->create(['name' => $this->faker->words(3, true)]);

        /** @var Address $address */
        $addressService = app()->make(AddressServiceInterface::class);

        $location = factory(Location::class)->create();

        $country = Country::create([
            'name'            => 'Australia',
            'iso_alpha2_code' => CountryHelper::getAlpha2Code('Australia'),
            'iso_alpha3_code' => CountryHelper::getAlpha3Code('Australia'),
        ]);

        $state = State::create([
            'country_id' => $country->id,
            'code'       => 'VIC',
            'name'       => StatesHelper::getStateNameByCode('VIC'),
        ]);

        $suburb = Suburb::create([
            'state_id' => $state->id,
            'name'     => 'Newport',
            'postcode' => 3015,
        ]);

        factory(LocationSuburb::class)->create([
            'location_id' => $location->id,
            'suburb_id'   => $suburb->id,
        ]);

        $address = $addressService->parseAddress('143 Mason St Newport VIC 3015');

        $address->suburb_id = $suburb->id;
        $address->saveOrFail();

        $fullAddress = preg_replace('/\n/', ' ', $address->getFullAddressAttribute());

        /** @var ContactPersonProfile $customer */
        $customer = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ])->person;
        /** @var JobContactAssignmentType $assignmentType */
        $assignmentType = factory(JobContactAssignmentType::class)
            ->create(['name' => SpecialJobContactAssignmentTypes::CUSTOMER]);

        // Preparing message body

        $messageBody = $this->faker->paragraphs(3, true);

        $messageBody = $this->injectStrings($messageBody, [
            'Service Type: ' . $serviceType->name,
            'Address: ' . $fullAddress,
            'Customer: ' . $customer->getFullName(),
        ]);

        /** @var Message $message */
        $message = factory(Message::class)->create(['message_body' => $messageBody]);

        /** @var Job $job */
        $job = factory(Job::class)->create();

        $this->jobMessagesService
            ->shouldReceive('matchMessageToJob')
            ->once()
            ->andReturnNull();

        $parserMock = \Mockery::mock('alias:' . Parser::class);
        $parserMock->shouldReceive('parseClaimNumber')
            ->between(1, 2)
            ->andReturnNull();

        $parserMock->shouldReceive('parseJobServiceType')
            ->once()
            ->with($message->message_body)
            ->andReturn($serviceType->name);

        $parserMock->shouldReceive('parseAddress')
            ->once()
            ->with($message->message_body)
            ->andReturn($fullAddress);

        $parserMock->shouldReceive('parseCustomer')
            ->once()
            ->with($message->message_body)
            ->andReturn($customer->getFullName());

        $this->service
            ->shouldReceive('createJob')
            ->once()
            ->withArgs(function (JobCreationData $data) use ($serviceType, $address, $location) {
                return $data->job_service_id === $serviceType->id &&
                    $data->site_address_id === $address->id &&
                    $data->owner_location_id === $location->id &&
                    $data->assigned_location_id === $location->id;
            })
            ->andReturn($job);

        $this->jobMessagesService
            ->shouldReceive('attachMessage')
            ->once()
            ->withArgs(function ($jobId, $messageId) use ($job, $message) {
                return $jobId === $job->id && $messageId === $message->id;
            });
        $this->jobContactsService
            ->shouldReceive('assignContact')
            ->once()
            ->withArgs(function ($jobId, $contactId, $typeId) use ($job, $customer, $assignmentType) {
                return
                    $jobId === $job->id &&
                    $contactId === $customer->contact_id &&
                    $typeId === $assignmentType->id;
            });

        $laravelJob = new FindOrCreateOrActivateJobForIncomingMessage($message->id);

        $laravelJob->handle(
            $this->messagingService,
            $this->service,
            $this->jobMessagesService,
            $this->jobContactsService,
            $this->jobNotesService
        );
    }

    public function testSuccessfulHandlingWhenMatchingJobIsClosedLessThenMonthAgo()
    {
        /** @var Message $message */
        $message = factory(Message::class)->create();

        /** @var Job $job */
        $job = $this->fakeJobWithStatus($this->faker->randomElement(JobStatuses::$closedStatuses));

        $this->jobMessagesService
            ->shouldReceive('matchMessageToJob')
            ->once()
            ->andReturn($job);

        $this->jobMessagesService
            ->shouldReceive('hasMessage')
            ->once()
            ->andReturnFalse();

        $this->jobMessagesService
            ->shouldReceive('attachMessage')
            ->once()
            ->with($job->id, $message->id)
            ->andReturnUsing(function ($jobId, $messageId) use ($job, $message) {
                return $jobId === $job->id && $messageId === $message->id;
            });

        $laravelJob = new FindOrCreateOrActivateJobForIncomingMessage($message->id);

        $laravelJob->handle(
            $this->messagingService,
            $this->service,
            $this->jobMessagesService,
            $this->jobContactsService,
            $this->jobNotesService
        );

        self::assertEquals($job->getCurrentStatus(), JobStatuses::IN_PROGRESS);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testSuccessfulHandlingWhenMatchingJobIsClosedMoreThenMonthAgo()
    {
        /** @var Message $message */
        $message = factory(Message::class)->create();

        $status = $this->faker->randomElement(JobStatuses::$closedStatuses);

        /** @var Job $job */
        $job                   = $this->fakeJobWithStatus($status, [
            'created_at' => Carbon::now()->subYear(),
        ]);
        $jobStatus             = $job->latestStatus;
        $jobStatus->created_at = Carbon::now()->subYear()->toDateTimeString();
        $jobStatus->save();

        $this->jobMessagesService
            ->shouldReceive('matchMessageToJob')
            ->once()
            ->andReturn($job);

        $this->jobMessagesService
            ->shouldReceive('hasMessage')
            ->once()
            ->andReturnFalse();

        $this->jobMessagesService
            ->shouldReceive('attachMessage')
            ->once()
            ->with($job->id, $message->id)
            ->andReturnUsing(function ($jobId, $messageId) use ($job, $message) {
                return $jobId === $job->id && $messageId === $message->id;
            });

        $laravelJob = new FindOrCreateOrActivateJobForIncomingMessage($message->id);

        $laravelJob->handle(
            $this->messagingService,
            $this->service,
            $this->jobMessagesService,
            $this->jobContactsService,
            $this->jobNotesService
        );

        self::assertEquals($job->getCurrentStatus(), JobStatuses::IN_PROGRESS);
    }
}
