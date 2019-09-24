<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Finance\Models\InvoiceItem;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\Jobs\Models\JobDocument;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobMessage;
use App\Components\Jobs\Models\JobNote;
use App\Components\Jobs\Models\JobPhoto;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobRoom;
use App\Components\Jobs\Models\MergedJob;
use App\Components\Jobs\Services\JobsMergeService;
use App\Models\User;
use Illuminate\Container\Container;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;
use Tests\Unit\UsageAndActuals\EquipmentTestFactory;

/**
 * Class JobsMergeServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 */
class JobsMergeServiceTest extends TestCase
{
    use JobFaker;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Job
     */
    private $sourceJob;

    /**
     * @var Job
     */
    private $destinationJob;

    /**
     * @var \App\Components\Jobs\Services\JobsMergeService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(JobsMergeService::class);

        $this->user           = factory(User::class)->create();
        $this->sourceJob      = $this->fakeJobWithStatus(JobStatuses::NEW);
        $this->destinationJob = $this->fakeJobWithStatus(JobStatuses::NEW);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobs()
    {
        $noteCnt   = $this->faker->numberBetween(1, 3);
        $msgCnt    = $this->faker->numberBetween(1, 3);
        $photosCnt = $this->faker->numberBetween(1, 3);

        factory(JobNote::class, $noteCnt)
            ->create(['job_id' => $this->sourceJob->id]);

        factory(JobMessage::class, $msgCnt)
            ->create(['job_id' => $this->sourceJob->id]);

        factory(JobPhoto::class, $photosCnt)->create([
            'job_id' => $this->sourceJob->id,
        ]);

        factory(JobNote::class)->create(['job_id' => $this->destinationJob->id]);
        factory(JobMessage::class)->create(['job_id' => $this->destinationJob->id]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($noteCnt + 1, $this->sourceJob->notes()->count());
        self::assertEquals(1, $this->sourceJob->notes()->where('mergeable', false)->count());
        self::assertEquals($noteCnt, $this->sourceJob->notes()->where('mergeable', true)->count());

        self::assertEquals(JobStatuses::NEW, $this->destinationJob->getCurrentStatus());
        self::assertEquals($noteCnt + 2, $this->destinationJob->notes()->count());
        self::assertEquals(1, $this->destinationJob->notes()->where('mergeable', false)->count());
        self::assertEquals($noteCnt + 1, $this->destinationJob->notes()->where('mergeable', true)->count());

        self::assertEquals(JobStatuses::CLOSED, $this->sourceJob->getCurrentStatus());
        self::assertEquals($msgCnt + 1, $this->destinationJob->messages()->count());
        self::assertEquals($msgCnt, $this->sourceJob->messages()->count());

        self::assertEquals($photosCnt, $this->sourceJob->photos()->count());
        self::assertEquals($photosCnt, $this->destinationJob->photos()->count());

        $sourcePhotoIds      = $this->sourceJob->photos()->pluck('id');
        $destinationPhotoIds = $this->destinationJob->photos()->pluck('id');

        self::assertEquals($sourcePhotoIds, $destinationPhotoIds);
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsPhotos()
    {
        $photosCnt = $this->faker->numberBetween(1, 3);

        factory(JobPhoto::class, $photosCnt)->create([
            'job_id' => $this->sourceJob->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        $sourcePhotos      = $this->sourceJob->photos();
        $destinationPhotos = $this->destinationJob->photos();

        self::assertEquals($photosCnt, $this->sourceJob->photos()->count());
        self::assertEquals($photosCnt, $this->destinationJob->photos()->count());

        self::assertEquals($sourcePhotos->pluck('id'), $destinationPhotos->pluck('id'));
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsLabours()
    {
        factory(JobLabour::class)->create([
            'job_id'          => $this->sourceJob->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create()->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($this->sourceJob->labours()->count(), $this->destinationJob->labours()->count());

        /** @var JobLabour $sourceJobLabour */
        $sourceJobLabour = $this->sourceJob->labours()->first();

        /** @var JobLabour $destinationJobLabour */
        $destinationJobLabour = $this->destinationJob->labours()->first();

        self::assertNotNull($sourceJobLabour->invoice_item_id);
        self::assertNull($destinationJobLabour->invoice_item_id);

        self::assertNotEquals($sourceJobLabour->id, $destinationJobLabour->id);
        self::assertNotEquals($sourceJobLabour->job_id, $destinationJobLabour->job_id);

        self::assertEquals($sourceJobLabour->labour_type_id, $destinationJobLabour->labour_type_id);
        self::assertEquals($sourceJobLabour->ended_at, $destinationJobLabour->ended_at);
        self::assertEquals($sourceJobLabour->ended_at_override, $destinationJobLabour->ended_at_override);
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsMaterial()
    {
        factory(JobMaterial::class)->create([
            'job_id'          => $this->sourceJob->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create()->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($this->sourceJob->materials()->count(), $this->destinationJob->materials()->count());

        /** @var JobMaterial $sourceJobMaterial */
        $sourceJobMaterial = $this->sourceJob->materials()->first();

        /** @var JobMaterial $destinationJobMaterial */
        $destinationJobMaterial = $this->destinationJob->materials()->first();

        self::assertNotNull($sourceJobMaterial->invoice_item_id);
        self::assertNull($destinationJobMaterial->invoice_item_id);

        self::assertNotEquals($sourceJobMaterial->id, $destinationJobMaterial->id);
        self::assertNotEquals($sourceJobMaterial->job_id, $destinationJobMaterial->job_id);

        self::assertEquals($sourceJobMaterial->material_id, $destinationJobMaterial->material_id);
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsEquipment()
    {
        $this->user           = factory(User::class)->create();
        $this->sourceJob      = $this->fakeJobWithStatus(JobStatuses::NEW);
        $this->destinationJob = $this->fakeJobWithStatus(JobStatuses::NEW);

        $equipment = EquipmentTestFactory::createEquipmentWithInterval();

        EquipmentTestFactory::createJobEquipmentWithInterval(
            $this->sourceJob->id,
            [
                'invoice_item_id' => factory(InvoiceItem::class)->create()->id,
                'equipment_id'    => $equipment->id,
            ]
        );

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($this->sourceJob->equipment()->count(), $this->destinationJob->equipment()->count());

        /** @var JobEquipment $sourceJobEquipment */
        $sourceJobEquipment = $this->sourceJob->equipment
            ->first();

        /** @var JobEquipment $destinationJobEquipment */
        $destinationJobEquipment = $this->destinationJob->equipment
            ->first();

        self::assertNotNull($sourceJobEquipment->invoice_item_id);
        self::assertNull($destinationJobEquipment->invoice_item_id);

        self::assertNotEquals($sourceJobEquipment->id, $destinationJobEquipment->id);
        self::assertNotEquals($sourceJobEquipment->job_id, $destinationJobEquipment->job_id);

        self::assertEquals($sourceJobEquipment->equipment_id, $destinationJobEquipment->equipment_id);
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsAllowances()
    {
        $jobAllowanceCnt = $this->faker->numberBetween(1, 3);

        factory(JobAllowance::class, $jobAllowanceCnt)->create([
            'job_id' => $this->sourceJob->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($jobAllowanceCnt, $this->sourceJob->allowances()->count());
        self::assertEquals($jobAllowanceCnt, $this->destinationJob->allowances()->count());
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsReimbursments()
    {
        $jobReimbursmentCnt = $this->faker->numberBetween(1, 3);

        factory(JobReimbursement::class, $jobReimbursmentCnt)->create([
            'job_id'          => $this->sourceJob->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create()->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($jobReimbursmentCnt, $this->sourceJob->reimbursements()->count());
        self::assertEquals($jobReimbursmentCnt, $this->destinationJob->reimbursements()->count());

        foreach ($this->sourceJob->reimbursements as $reimbursement) {
            self::assertNotNull($reimbursement->invoice_item_id);
        }

        foreach ($this->destinationJob->reimbursements as $reimbursement) {
            self::assertNull($reimbursement->invoice_item_id);
        }
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsLahaCompensations()
    {
        $jobLahaCompensationsCnt = $this->faker->numberBetween(1, 3);

        factory(JobLahaCompensation::class, $jobLahaCompensationsCnt)->create([
            'job_id' => $this->sourceJob->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($jobLahaCompensationsCnt, $this->sourceJob->compensations()->count());
        self::assertEquals($jobLahaCompensationsCnt, $this->destinationJob->compensations()->count());
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsRooms()
    {
        factory(JobRoom::class)->create([
            'job_id' => $this->sourceJob->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($this->sourceJob->jobRooms()->count(), $this->destinationJob->jobRooms()->count());

        /** @var JobRoom $sourceJobRoom */
        $sourceJobRoom = $this->sourceJob->jobRooms
            ->first();

        /** @var JobEquipment $destinationJobRoom */
        $destinationJobRoom = $this->destinationJob->jobRooms
            ->first();

        self::assertNotEquals($sourceJobRoom->id, $destinationJobRoom->id);
        self::assertNotEquals($sourceJobRoom->job_id, $destinationJobRoom->job_id);
        self::assertEquals($this->sourceJob->id, $sourceJobRoom->job_id);
        self::assertEquals($this->destinationJob->id, $destinationJobRoom->job_id);
    }

    /**
     * @throws \Throwable
     */
    public function testMergeDocumentsWithTypeJobs()
    {
        $documentCnt = $this->faker->numberBetween(1, 3);

        factory(JobDocument::class, $documentCnt)->create([
            'job_id' => $this->sourceJob->id,
        ]);

        factory(JobDocument::class, $documentCnt)->create([
            'job_id' => $this->destinationJob->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($documentCnt, $this->sourceJob->documents()->count());
        self::assertEquals(2 * $documentCnt, $this->destinationJob->documents()->count());

        foreach ($this->destinationJob->documents()->get() as $d) {
            self::assertNotNull($d->pivot->type);
        }
    }

    /**
     * @throws \Throwable
     */
    public function testMergeTheSameDocumentsWithTypeJobs()
    {
        $jobDocument = factory(JobDocument::class)->create([
            'job_id' => $this->sourceJob->id,
        ]);

        factory(JobDocument::class)->create([
            'job_id'      => $this->destinationJob->id,
            'document_id' => $jobDocument->document_id,
            'type'        => $jobDocument->type,
            'creator_id'  => $jobDocument->creator_id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals(1, $this->sourceJob->documents()->count());
        self::assertEquals(1, $this->destinationJob->documents()->count());
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsWithoutMergeableNotes()
    {
        factory(JobNote::class)->create([
            'job_id'    => $this->sourceJob->id,
            'mergeable' => false,
        ]);

        factory(JobNote::class)->create([
            'job_id'    => $this->sourceJob->id,
            'mergeable' => true,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals(3, $this->sourceJob->notes()->count());
        self::assertEquals(2, $this->sourceJob->notes()->where('mergeable', false)->count());
        self::assertEquals(1, $this->sourceJob->notes()->where('mergeable', true)->count());

        self::assertEquals(2, $this->destinationJob->notes()->count());
        self::assertEquals(1, $this->destinationJob->notes()->where('mergeable', false)->count());
        self::assertEquals(1, $this->destinationJob->notes()->where('mergeable', true)->count());
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobsWithoutMergeableNotesThatAlreadyBeenMerged()
    {
        $notMergeableNotesCount = $this->faker->numberBetween(3, 5);
        factory(JobNote::class, $notMergeableNotesCount)->create([
            'job_id'    => $this->sourceJob->id,
            'mergeable' => false,
        ]);

        factory(JobNote::class)->create([
            'job_id'    => $this->sourceJob->id,
            'mergeable' => true,
        ]);

        factory(MergedJob::class)->create([
            'source_job_id'      => $this->sourceJob->id,
            'destination_job_id' => $this->destinationJob->id,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals($notMergeableNotesCount + 2, $this->sourceJob->notes()->count());
        self::assertEquals(
            $notMergeableNotesCount + 1,
            $this->sourceJob->notes()->where('mergeable', false)->count()
        );
        self::assertEquals(1, $this->sourceJob->notes()->where('mergeable', true)->count());

        self::assertEquals(2, $this->destinationJob->notes()->count());
        self::assertEquals(1, $this->destinationJob->notes()->where('mergeable', false)->count());
        self::assertEquals(1, $this->destinationJob->notes()->where('mergeable', false)->count());
    }

    /**
     * @throws \Throwable
     */
    public function testRecursiveMergeJobs()
    {
        $destinationJob2 = $this->fakeJobWithStatus(JobStatuses::NEW);

        factory(JobNote::class)->create([
            'job_id'    => $this->sourceJob->id,
            'mergeable' => true,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);
        $this->service->mergeJobs($this->destinationJob->id, $destinationJob2->id, $this->user->id);

        self::assertEquals(2, $this->sourceJob->notes()->count());
        self::assertEquals(1, $this->sourceJob->notes()->where('mergeable', true)->count());
        self::assertEquals(1, $this->sourceJob->notes()->where('mergeable', false)->count());

        self::assertEquals(3, $this->destinationJob->notes()->count());
        self::assertEquals(1, $this->destinationJob->notes()->where('mergeable', true)->count());
        self::assertEquals(2, $this->destinationJob->notes()->where('mergeable', false)->count());

        self::assertEquals(2, $destinationJob2->notes()->count());
        self::assertEquals(1, $destinationJob2->notes()->where('mergeable', true)->count());
        self::assertEquals(1, $destinationJob2->notes()->where('mergeable', false)->count());
    }

    /**
     * @throws \Throwable
     */
    public function testSequentialMergeJobsToOneDestination()
    {
        $sourceJob2 = $this->fakeJobWithStatus(JobStatuses::NEW);

        factory(JobNote::class)->create([
            'job_id'    => $this->sourceJob->id,
            'mergeable' => true,
        ]);

        factory(JobNote::class)->create([
            'job_id'    => $sourceJob2->id,
            'mergeable' => true,
        ]);

        $this->service->mergeJobs($this->sourceJob->id, $this->destinationJob->id, $this->user->id);
        $this->service->mergeJobs($sourceJob2->id, $this->destinationJob->id, $this->user->id);

        self::assertEquals(2, $this->sourceJob->notes()->count());
        self::assertEquals(1, $this->sourceJob->notes()->where('mergeable', true)->count());
        self::assertEquals(1, $this->sourceJob->notes()->where('mergeable', false)->count());

        self::assertEquals(2, $sourceJob2->notes()->count());
        self::assertEquals(1, $sourceJob2->notes()->where('mergeable', true)->count());
        self::assertEquals(1, $sourceJob2->notes()->where('mergeable', false)->count());

        self::assertEquals(4, $this->destinationJob->notes()->count());
        self::assertEquals(2, $this->destinationJob->notes()->where('mergeable', true)->count());
        self::assertEquals(2, $this->destinationJob->notes()->where('mergeable', false)->count());
    }
}
