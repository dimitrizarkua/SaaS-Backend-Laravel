<?php

namespace Tests\Unit\Contacts;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Events\NoteAttachedToContact;
use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactNote;
use App\Components\Contacts\Models\ContactStatus;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Meetings\Models\Meeting;
use App\Components\Notes\Models\Note;
use App\Components\Tags\Models\Tag;
use App\Exceptions\Api\NotAllowedException;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class ContactsServiceTest
 *
 * @package Tests\Unit\ContactsServiceTest
 * @group   contacts
 * @group   services
 */
class ContactsServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \App\Components\Contacts\Interfaces\ContactsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('ContactsSeeder');
        $this->service = Container::getInstance()->make(ContactsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testCreatePerson()
    {
        $personData = FakeContactsDataFactory::getPersonDataInstance();

        $createdContact = $this->service->createPerson($personData);
        $person         = $createdContact->person()->first();
        self::assertEquals(ContactTypes::PERSON, $createdContact->contact_type);
        self::assertEquals(
            $personData->getFirstName(),
            $person->first_name
        );
        self::assertEquals(
            $personData->getLastName(),
            $person->last_name
        );
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testCheckNewPersonHasActiveStatus()
    {
        $personData     = FakeContactsDataFactory::getPersonDataInstance();
        $createdContact = $this->service->createPerson($personData);

        $status = ContactStatus::query()
            ->where('contact_id', $createdContact->id)
            ->firstOrFail()
            ->status;

        self::assertEquals($status, $createdContact->latestStatus->status);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testCheckNewCustomerHasLeadStatus()
    {
        $customerData = FakeContactsDataFactory::getCustomerDataInstance();
        $createdContact = $this->service->createPerson($customerData);

        $leadStatus = ContactStatus::query()
            ->where('contact_id', $createdContact->id)
            ->firstOrFail()
            ->status;

        self::assertEquals($leadStatus, $createdContact->latestStatus->status);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testCreateFullPerson()
    {
        $personData = FakeContactsDataFactory::getFullPersonDataInstance();

        $createdPerson = $this->service->createPerson($personData);

        self::assertEquals(ContactTypes::PERSON, $createdPerson->contact_type);
        $person = $createdPerson->person()->first();

        self::assertEquals($personData->getFirstName(), $person->first_name);
        self::assertEquals($personData->getLastName(), $person->last_name);
        self::assertEquals($personData->getJobTitle(), $person->job_title);
        self::assertEquals($personData->getDirectPhone(), $person->direct_phone);
        self::assertEquals($personData->getMobilePhone(), $person->mobile_phone);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToCreatePersonWithoutFirstName()
    {
        $personData             = FakeContactsDataFactory::getPersonDataInstance();
        $personData->first_name = null;

        self::expectException(\TypeError::class);
        $this->service->createPerson($personData);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToCreatePersonWithoutLastName()
    {
        $personData            = FakeContactsDataFactory::getPersonDataInstance();
        $personData->last_name = null;

        self::expectException(\TypeError::class);
        $this->service->createPerson($personData);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateCompany()
    {
        $companyData = FakeContactsDataFactory::getCompanyDataInstance();

        $createdCompany = $this->service->createCompany($companyData);

        self::assertEquals(ContactTypes::COMPANY, $createdCompany->contact_type);

        $company = $createdCompany->company()->first();
        self::assertEquals($companyData->getAbn(), $company->abn);
        self::assertEquals($companyData->getLegalName(), $company->legal_name);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateFullCompany()
    {
        $companyData = FakeContactsDataFactory::getFullCompanyDataInstance();

        $createdCompany = $this->service->createCompany($companyData);

        $company = $createdCompany->company()->first();
        self::assertEquals($companyData->getAbn(), $company->abn);
        self::assertEquals($companyData->getLegalName(), $company->legal_name);
        self::assertEquals($companyData->getWebsite(), $company->website);
        self::assertEquals($companyData->getTradingName(), $company->trading_name);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testLinkPersonToCompany()
    {
        $companyData = FakeContactsDataFactory::getCompanyDataInstance();
        $personData  = FakeContactsDataFactory::getPersonDataInstance();

        $createdCompany = $this->service->createCompany($companyData);
        $createdPerson  = $this->service->createPerson($personData);

        $this->service->linkContacts($createdCompany->id, $createdPerson->id);

        self::assertEquals(1, $createdCompany->subsidiaries()->count());
        self::assertEquals(1, $createdPerson->headoffices()->count());
    }

    /**
     * @throws \Throwable
     */
    public function testLinkCompanyToCompany()
    {
        $companyData1 = FakeContactsDataFactory::getCompanyDataInstance();
        $companyData2 = FakeContactsDataFactory::getCompanyDataInstance();

        $createdCompany1 = $this->service->createCompany($companyData1);
        $createdCompany2 = $this->service->createCompany($companyData2);

        $this->service->linkContacts($createdCompany1->id, $createdCompany2->id);

        self::assertEquals(1, $createdCompany1->subsidiaries()->count());
        self::assertEquals(1, $createdCompany2->headoffices()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testLinkFailToLinkCompanyToPerson()
    {
        $companyData = FakeContactsDataFactory::getCompanyDataInstance();
        $personData  = FakeContactsDataFactory::getPersonDataInstance();

        $createdCompany = $this->service->createCompany($companyData);
        $createdPerson  = $this->service->createPerson($personData);

        self::expectException(NotAllowedException::class);
        $this->service->linkContacts($createdPerson->id, $createdCompany->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testSuccessToLinkPersonToCompanyThatAlreadyWasLinked()
    {
        $companyData = FakeContactsDataFactory::getCompanyDataInstance();
        $personData  = FakeContactsDataFactory::getPersonDataInstance();

        $createdCompany = $this->service->createCompany($companyData);
        $createdPerson  = $this->service->createPerson($personData);
        $this->service->linkContacts($createdCompany->id, $createdPerson->id);
        $this->service->linkContacts($createdCompany->id, $createdPerson->id);

        self::assertCount(1, $createdCompany->subsidiaries);
        self::assertEquals($createdPerson->id, $createdCompany->subsidiaries[0]->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testSuccessToLinkPersonToCompanyThatAlreadyWasLinkedToAnotherCompany()
    {
        $companyData        = FakeContactsDataFactory::getCompanyDataInstance();
        $companyAnotherData = FakeContactsDataFactory::getCompanyDataInstance();
        $personData         = FakeContactsDataFactory::getPersonDataInstance();

        $createdCompany        = $this->service->createCompany($companyData);
        $createdAnotherCompany = $this->service->createCompany($companyAnotherData);
        $createdPerson         = $this->service->createPerson($personData);
        $this->service->linkContacts($createdCompany->id, $createdPerson->id);
        $this->service->linkContacts($createdAnotherCompany->id, $createdPerson->id);

        self::assertCount(0, $createdCompany->subsidiaries);
        self::assertCount(1, $createdAnotherCompany->subsidiaries);
        self::assertEquals($createdPerson->id, $createdAnotherCompany->subsidiaries[0]->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testUnLinkPersonFromCompany()
    {
        $companyData = FakeContactsDataFactory::getCompanyDataInstance();
        $personData  = FakeContactsDataFactory::getPersonDataInstance();

        $createdCompany = $this->service->createCompany($companyData);
        $createdPerson  = $this->service->createPerson($personData);
        $this->service->linkContacts($createdCompany->id, $createdPerson->id);
        $this->service->unlinkContacts($createdCompany->id, $createdPerson->id);

        self::assertEquals(0, $createdCompany->subsidiaries()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testAddNote()
    {
        $personData = FakeContactsDataFactory::getPersonDataInstance();
        $note       = factory(Note::class)->create();

        $createdPerson = $this->service->createPerson($personData);
        $this->service->addNote($createdPerson->id, $note->id);

        self::assertEquals(1, $createdPerson->notes()->count());
    }

    public function testAddNoteAndCatchEventNoteAttachedToContact()
    {
        $contact = factory(Contact::class)->create();
        $note    = factory(Note::class)->create();

        Event::fake();

        $this->service->addNote($contact->id, $note->id);

        Event::assertDispatched(NoteAttachedToContact::class, function ($event) use ($contact) {
            return $event->targetModel->id === $contact->id;
        });

        self::assertEquals(1, $contact->notes()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToAddDuplicateNote()
    {
        $personData = FakeContactsDataFactory::getPersonDataInstance();
        $note       = factory(Note::class)->create();

        $createdPerson = $this->service->createPerson($personData);

        self::expectException(NotAllowedException::class);
        $this->service->addNote($createdPerson->id, $note->id);
        $this->service->addNote($createdPerson->id, $note->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testAddNoteWithMeeting()
    {
        $personData = FakeContactsDataFactory::getPersonDataInstance();
        $note       = factory(Note::class)->create();
        $meeting    = factory(Meeting::class)->create();

        $createdPerson = $this->service->createPerson($personData);
        $this->service->addNote($createdPerson->id, $note->id, $meeting->id);

        self::assertEquals(1, $createdPerson->notes()->count());

        $contactNote = ContactNote::where('contact_id', $createdPerson->id)
            ->where('note_id', $note->id)
            ->firstOrFail();

        self::assertEquals($meeting->id, $contactNote->meeting_id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testAddAddress()
    {
        $personData  = FakeContactsDataFactory::getPersonDataInstance();
        $address     = factory(Address::class)->create();
        $addressType = $this->faker->randomElement(AddressContactTypes::values());

        $createdPerson = $this->service->createPerson($personData);
        $this->service->addAddress($createdPerson->id, $address->id, $addressType);

        self::assertEquals(1, $createdPerson->addresses()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToAddDuplicateAddress()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);
        $address       = factory(Address::class)->create();
        $addressType   = $this->faker->randomElement(AddressContactTypes::values());

        self::expectException(NotAllowedException::class);
        $this->service->addAddress($createdPerson->id, $address->id, $addressType);
        $this->service->addAddress($createdPerson->id, $address->id, $addressType);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testDeleteAddress()
    {
        $personData  = FakeContactsDataFactory::getPersonDataInstance();
        $address     = factory(Address::class)->create();
        $addressType = $this->faker->randomElement(AddressContactTypes::values());

        $createdPerson = $this->service->createPerson($personData);
        $this->service->addAddress($createdPerson->id, $address->id, $addressType);
        $this->service->deleteAddress($createdPerson->id, $address->id);

        self::assertEquals(0, $createdPerson->addresses()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testAddTag()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);
        $tag           = factory(Tag::class)->create();

        $this->service->addTag($createdPerson->id, $tag->id);

        self::assertEquals(1, $createdPerson->tags()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToAddDuplicateTag()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);
        $tag           = factory(Tag::class)->create();

        self::expectException(NotAllowedException::class);
        $this->service->addTag($createdPerson->id, $tag->id);
        $this->service->addTag($createdPerson->id, $tag->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testDeleteTag()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);
        $tag           = factory(Tag::class)->create();

        $this->service->addTag($createdPerson->id, $tag->id);
        $this->service->deleteTag($createdPerson->id, $tag->id);

        self::assertEquals(0, $createdPerson->tags()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testAddManagedAccount()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);
        $user          = factory(User::class)->create();

        $this->service->addManagedAccount($createdPerson->id, $user->id);

        self::assertEquals(1, $createdPerson->managedAccounts()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToAddDuplicateManagedAccount()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);
        $user          = factory(User::class)->create();

        self::expectException(NotAllowedException::class);
        $this->service->addManagedAccount($createdPerson->id, $user->id);
        $this->service->addManagedAccount($createdPerson->id, $user->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testDeleteManagedAccount()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);
        $user          = factory(User::class)->create();

        $this->service->addManagedAccount($createdPerson->id, $user->id);
        $this->service->deleteManagedAccount($createdPerson->id, $user->id);

        self::assertEquals(0, $createdPerson->managedAccounts()->count());
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testTouchContact()
    {
        $personData    = FakeContactsDataFactory::getPersonDataInstance();
        $createdPerson = $this->service->createPerson($personData);

        $this->service->touch($createdPerson->id);

        $touchedContact = Contact::findOrFail($createdPerson->id);
        self::assertNotNull($touchedContact->last_active_at);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function getDefaultStatus()
    {
        $person = FakeContactsDataFactory::getPersonDataInstance();

        $createdPerson = $this->service->createPerson($person);
        $defaultStatus = $this->service->getDefaultStatus($createdPerson->id);

        self::assertEquals(ContactStatuses::ACTIVE, $defaultStatus);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function getDefaultStatusForCustomer()
    {
        $customer = FakeContactsDataFactory::getCustomerDataInstance();

        $createdCustomer = $this->service->createPerson($customer);
        $defaultStatus   = $this->service->getDefaultStatus($createdCustomer->id);

        self::assertEquals(ContactStatuses::LEAD, $defaultStatus);
    }
}
