<?php

namespace App\Jobs\Jobs;

use App\Components\Addresses\Interfaces\AddressServiceInterface;
use App\Components\Contacts\Models\ContactPersonProfile;
use App\Components\Jobs\Interfaces\JobContactsServiceInterface;
use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\JobContactAssignmentType;
use App\Components\Jobs\Models\JobService;
use App\Components\Jobs\Models\VO\JobCreationData;
use App\Components\Messages\Enums\SpecialJobContactAssignmentTypes;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Models\NoteData;
use App\Core\Utils\Parser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class FindOrCreateOrActivateJobForIncomingMessage
 *
 * @package App\Jobs\Jobs
 */
class FindOrCreateOrActivateJobForIncomingMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $messageId;

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * Create a new job instance.
     *
     * @param int $messageId Message id.
     */
    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Helper method that infers job service type from input string.
     *
     * @param string $input Input string.
     *
     * @return \App\Components\Jobs\Models\JobService|null
     */
    private function inferJobServiceType(string $input): ?JobService
    {
        $type = Parser::parseJobServiceType($input);
        if (null === $type) {
            return null;
        }

        return JobService::query()
            ->whereRaw(sprintf('LOWER(name) = \'%s\'', strtolower(trim($type))))
            ->first();
    }

    /**
     * Helper method that infers site location address from input string.
     *
     * @param string $input Input string.
     *
     * @throws \Throwable
     *
     * @return array|null
     */
    private function inferSiteLocationAddress(string $input): ?array
    {
        $inputAddress = Parser::parseAddress($input);
        if (null === $inputAddress) {
            return ['address_id' => null, 'location_id' => null];
        }

        $addressService = app()->make(AddressServiceInterface::class);

        $parsedAddress = $addressService->parseAddress($inputAddress);

        if (empty($parsedAddress->id)) {
            $parsedAddress->saveOrFail();
        }

        $locations  = $addressService->getAddressLocations($parsedAddress);
        $locationId = (!$locations->isEmpty()) ? $locations->first()->location_id : null;

        return ['address_id' => $parsedAddress->id, 'location_id' => $locationId];
    }

    /**
     * Helper method that infers customer contact from input string.
     *
     * @param string $input Input string.
     *
     * @return \App\Components\Contacts\Models\ContactPersonProfile|null
     */
    private function inferCustomer(string $input): ?ContactPersonProfile
    {
        $customerName = Parser::parseCustomer($input);
        if (null === $customerName) {
            return null;
        }

        // TODO: This should be searched with Elastic Search

        return ContactPersonProfile::query()
            ->whereRaw('LOWER(CONCAT_WS(\' \', "first_name", "last_name")) = :name', [
                'name' => strtolower(trim($customerName))
            ])
            ->first();
    }

    /**
     * Execute the job.
     *
     * @param MessagingServiceInterface                                   $messagingService Messaging service.
     * @param \App\Components\Jobs\Interfaces\JobsServiceInterface        $jobsService
     * @param \App\Components\Jobs\Interfaces\JobMessagesServiceInterface $jobMessagesService
     * @param \App\Components\Jobs\Interfaces\JobContactsServiceInterface $jobContactsService
     * @param \App\Components\Jobs\Interfaces\JobNotesServiceInterface    $jobNotesService
     *
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function handle(
        MessagingServiceInterface $messagingService,
        JobsServiceInterface $jobsService,
        JobMessagesServiceInterface $jobMessagesService,
        JobContactsServiceInterface $jobContactsService,
        JobNotesServiceInterface $jobNotesService
    ) {
        $job = $jobMessagesService->matchMessageToJob($this->messageId);
        // Found existing job, not closed/canceled more then REOPEN_BY_INCOMING_MESSAGE_DAYS days ago,
        // linking message...
        if (null !== $job && (!$job->isClosed() || $job->reopen())) {
            if (!$jobMessagesService->hasMessage($job->id, $this->messageId)) {
                $jobMessagesService->attachMessage($job->id, $this->messageId);

                Log::info(
                    sprintf(
                        'Incoming message [MESSAGE_ID:%d] has been linked to existing job [JOB_ID:%d].',
                        $this->messageId,
                        $job->id
                    ),
                    [
                        'job_id'     => $job->id,
                        'message_id' => $this->messageId,
                    ]
                );
            } else {
                Log::info(
                    sprintf(
                        'Incoming message [MESSAGE_ID:%d] already linked to existing job [JOB_ID:%d].',
                        $this->messageId,
                        $job->id
                    ),
                    [
                        'job_id'     => $job->id,
                        'message_id' => $this->messageId,
                    ]
                );
            }

            return;
        }

        // Otherwise creating new job and linking message to it.
        $message = $messagingService->getMessage($this->messageId);

        $claimNumber = Parser::parseClaimNumber($message->subject) ??
            Parser::parseClaimNumber($message->message_body);

        $jobData = new JobCreationData();
        if (!$job) {
            $jobData->setClaimNumber($claimNumber);
        }

        $serviceType = $this->inferJobServiceType($message->message_body);
        if ($serviceType) {
            $jobData->job_service_id = $serviceType->id;
        }

        $addressLocation = $this->inferSiteLocationAddress($message->message_body);
        if ($addressLocation) {
            $jobData->site_address_id      = $addressLocation['address_id'];
            $jobData->owner_location_id    = $addressLocation['location_id'];
            $jobData->assigned_location_id = $addressLocation['location_id'];
        }

        DB::transaction(function () use (
            $jobsService,
            $jobMessagesService,
            $jobNotesService,
            $jobData,
            &$job,
            &$message
        ) {
            $closedJob = $job;
            $job       = $jobsService->createJob($jobData);

            if (null !== $closedJob) {
                $note = $this->getNoteService()->addNote(new NoteData(
                    sprintf('This job was created as a result of receiving incoming message related to a job #%s
                    which was closed more than 30 days ago.
                    To merge both jobs, choose this job as the destination when merging.', $closedJob->id)
                ));

                $jobNotesService->addNote($job->id, $note->id);
            }

            $jobMessagesService->attachMessage($job->id, $message->id);
        });

        $customer = $this->inferCustomer($message->message_body);
        if ($customer) {
            /** @var JobContactAssignmentType $assignmentType */
            $assignmentType = JobContactAssignmentType::query()
                ->where('name', SpecialJobContactAssignmentTypes::CUSTOMER)
                ->first();
            if ($assignmentType) {
                $jobContactsService->assignContact($job->id, $customer->contact_id, $assignmentType->id);
            }
        }

        Log::info(
            sprintf(
                'New job [JOB_ID:%d] has been created for incoming message [MESSAGE_ID:%d].',
                $job->id,
                $message->id
            ),
            [
                'job_id'     => $job->id,
                'message_id' => $message->id,
            ]
        );
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addSeconds(5);
    }


    /**
     * @return \App\Components\Notes\Interfaces\NotesServiceInterface
     */
    private function getNoteService(): NotesServiceInterface
    {
        return app()->make(NotesServiceInterface::class);
    }
}
