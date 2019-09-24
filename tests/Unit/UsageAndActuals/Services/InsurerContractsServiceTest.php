<?php

namespace Tests\Unit\UsageAndActuals\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\UsageAndActuals\Exceptions\NotAllowedException;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Models\InsurerContract;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class InsurerContractsServiceTest
 *
 * @package Tests\Unit\UsageAndActuals\Services
 * @group   services
 */
class InsurerContractsServiceTest extends TestCase
{
    /**
     * @var \App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->app->make(InsurerContractsInterface::class);
    }

    public function testCreateSuccess()
    {
        $contractData = new InsurerContractData(
            [
                'contact_id'       => factory(Contact::class)->create()->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            ]
        );

        $insurerContract = $this->service->createContract($contractData);

        self::assertEquals($contractData->getContactId(), $insurerContract->contact->id);
        self::assertEquals($contractData->getContractNumber(), $insurerContract->contract_number);
        self::assertEquals($contractData->getDescription(), $insurerContract->description);
        self::assertEquals($contractData->getEffectDate(), $insurerContract->effect_date);
        self::assertEquals($contractData->getTerminationDate(), $insurerContract->termination_date);
    }

    public function testCreateFailWithInvalidDatePeriod()
    {
        $insurer = factory(Contact::class)->create();

        $this->service->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurer->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            ]
        ));

        $this->expectException(NotAllowedException::class);
        $this->service->createContract(new InsurerContractData(
            [
                'contact_id'      => $insurer->id,
                'contract_number' => $this->faker->bankAccountNumber,
                'description'     => $this->faker->text,
                'effect_date'     => Carbon::now()->subDays(1)->format('Y-m-d'),
            ]
        ));
    }

    public function testUpdateSuccess()
    {
        $insurerContract = factory(InsurerContract::class)->create();
        $contractData    = new InsurerContractData([
            'contact_id'       => factory(Contact::class)->create()->id,
            'contract_number'  => $this->faker->bankAccountNumber,
            'description'      => $this->faker->text,
            'effect_date'      => Carbon::now()->format('Y-m-d'),
            'termination_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ]);

        $insurerContract = $this->service->updateContract($insurerContract, $contractData);

        self::assertEquals($contractData['contact_id'], $insurerContract->contact->id);
        self::assertEquals($contractData['contract_number'], $insurerContract->contract_number);
        self::assertEquals($contractData['description'], $insurerContract->description);
        self::assertEquals($contractData->getEffectDate(), $insurerContract->effect_date);
        self::assertEquals($contractData->getTerminationDate(), $insurerContract->termination_date);
    }

    public function testUpdateSuccessWithoutContact()
    {
        $insurerContract = factory(InsurerContract::class)->create();
        $contractData    = new InsurerContractData([
            'contract_number'  => $this->faker->bankAccountNumber,
            'description'      => $this->faker->text,
            'effect_date'      => Carbon::now()->format('Y-m-d'),
            'termination_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ]);

        $insurerContract = $this->service->updateContract($insurerContract, $contractData);

        self::assertEquals($contractData['contract_number'], $insurerContract->contract_number);
        self::assertEquals($contractData['description'], $insurerContract->description);
        self::assertEquals($contractData->getEffectDate(), $insurerContract->effect_date);
        self::assertEquals($contractData->getTerminationDate(), $insurerContract->termination_date);
    }

    public function testUpdateSuccessWithoutDataPeriod()
    {
        $insurerContract = factory(InsurerContract::class)->create();
        $contractData    = new InsurerContractData([
            'contact_id'      => factory(Contact::class)->create()->id,
            'contract_number' => $this->faker->bankAccountNumber,
            'description'     => $this->faker->text,
        ]);

        $insurerContract = $this->service->updateContract($insurerContract, $contractData);

        self::assertEquals($contractData['contact_id'], $insurerContract->contact->id);
        self::assertEquals($contractData['contract_number'], $insurerContract->contract_number);
        self::assertEquals($contractData['description'], $insurerContract->description);
    }

    public function testUpdateFailWithInvalidDatePeriod()
    {
        $insurer = factory(Contact::class)->create();

        $this->service->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurer->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            ]
        ));

        $insurerContract = $this->service->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurer->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->addDays(11)->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
            ]
        ));

        $this->expectException(NotAllowedException::class);
        $this->service->updateContract(
            $insurerContract,
            new InsurerContractData([
                'contact_id'      => $insurer->id,
                'contract_number' => $this->faker->bankAccountNumber,
                'description'     => $this->faker->text,
                'effect_date'     => Carbon::now()->subDays(1)->format('Y-m-d'),
            ])
        );
    }

    public function testGetActiveContractForInsurerMethod()
    {
        $insurer = factory(Contact::class)->create();
        $this->service->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurer->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->subDays(10)->format('Y-m-d'),
                'termination_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            ]
        ));
        $actualContract = $this->service->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurer->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->subDays(3)->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            ]
        ));
        $this->service->createContract(new InsurerContractData(
            [
                'contact_id'      => $insurer->id,
                'contract_number' => $this->faker->bankAccountNumber,
                'description'     => $this->faker->text,
                'effect_date'     => Carbon::now()->addDays(10)->format('Y-m-d'),
            ]
        ));

        $contract = $this->service->getActiveContractForInsurer($insurer);

        self::assertEquals($actualContract->id, $contract->id);
        self::assertTrue($actualContract->isActive());
    }
}
