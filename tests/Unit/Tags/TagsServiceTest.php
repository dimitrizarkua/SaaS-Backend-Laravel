<?php

namespace Tests\Unit\Tags;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactTag;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderTag;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTag;
use App\Components\Tags\Enums\TagTypes;
use App\Components\Tags\Interfaces\TagsServiceInterface;
use App\Components\Tags\Mappers\TagTypesMapper;
use App\Components\Tags\Models\Tag;
use App\Exceptions\Api\NotAllowedException;
use App\Exceptions\Api\NotFoundException;
use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * Class TagsServiceTest
 *
 * @package Tests\Unit\Tags
 * @group   tags
 * @group   tags-service
 */
class TagsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Tags\Interfaces\TagsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(TagsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        \Mockery::close();
        unset($this->service);
    }

    public function testFailToAttachCauseUnknownTagType()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create([
            'type' => $this->faker->lexify('????'),
        ]);

        self::expectException(\RuntimeException::class);

        $this->service->attachTag($tag, 0);
    }

    public function testFailToAttachCauseEntityNotFound()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create([
            'type' => TagTypes::JOB,
        ]);

        self::expectException(NotFoundException::class);

        $this->service->attachTag($tag, 0);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAttachTagToJob()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create([
            'type' => TagTypes::JOB,
        ]);
        /** @var Job $job */
        $job = factory(Job::class)->create();

        $this->service->attachTag($tag, $job->id);

        JobTag::query()->where([
            'job_id' => $job->id,
            'tag_id' => $tag->id,
        ])->firstOrFail();
    }

    public function testFailToAttachDuplicateTag()
    {
        $type = $this->faker->randomElement([
            TagTypes::JOB,
            TagTypes::CONTACT,
            TagTypes::PURCHASE_ORDER,
        ]);
        /** @var Tag $tag */
        $tag         = factory(Tag::class)->create([
            'type' => $type,
        ]);
        $className   = TagTypesMapper::getMapping($type);
        $classEntity = factory($className)->create();

        self::expectException(NotAllowedException::class);

        $this->service->attachTag($tag, $classEntity->id);
        $this->service->attachTag($tag, $classEntity->id);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAttachTagToContact()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create([
            'type' => TagTypes::CONTACT,
        ]);
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();

        $this->service->attachTag($tag, $contact->id);

        ContactTag::query()->where([
            'contact_id' => $contact->id,
            'tag_id'     => $tag->id,
        ])->firstOrFail();
    }

    public function testAttachTagToCreditNote()
    {
        self::markTestSkipped("CreditNote model doesn't exist yet");
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAttachTagToPurchaseOrder()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create([
            'type' => TagTypes::PURCHASE_ORDER,
        ]);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();

        $this->service->attachTag($tag, $purchaseOrder->id);

        PurchaseOrderTag::query()->where([
            'purchase_order_id' => $purchaseOrder->id,
            'tag_id'            => $tag->id,
        ])->firstOrFail();
    }
}
