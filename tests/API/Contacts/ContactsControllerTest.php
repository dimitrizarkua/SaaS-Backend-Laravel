<?php

namespace Tests\API\Contacts;

use App\Components\Contacts\Events\ContactModelChanged;
use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Contacts\Models\ContactPersonProfile;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobContactAssignmentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\API\ApiTestCase;

/**
 * Class ContactsControllerTest
 *
 * @package Tests\API\Contacts
 * @group   contacts
 * @group   api
 */
class ContactsControllerTest extends ApiTestCase
{
    protected $permissions = ['contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete'];
    protected const CONTACT_CATEGORY_ID = '1234';

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetAllRecords()
    {
        $mock = Mockery::mock('alias:' . Contact::class);
        $mock->shouldReceive('filter')
            ->once()
            ->withArgs(function ($options) {
                return $options === [];
            });

        $url = action('Contacts\ContactsController@index');

        $this->getJson($url);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilterByContactType()
    {
        $mock = Mockery::mock('alias:' . Contact::class);
        $mock->shouldReceive('filter')
            ->once()
            ->withArgs(function ($options) {
                return $options === ['contact_type' => ContactTypes::PERSON];
            });

        $url = action('Contacts\ContactsController@index', [
            'contact_type' => ContactTypes::PERSON,
        ]);
        $this->getJson($url);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilterByCategoryId()
    {
        $mock = Mockery::mock('alias:' . Contact::class);
        $mock->shouldReceive('filter')
            ->once()
            ->withArgs(function ($options) {
                return $options === ['contact_category_id' => self::CONTACT_CATEGORY_ID];
            });

        $url = action('Contacts\ContactsController@index', [
            'contact_category_id' => self::CONTACT_CATEGORY_ID,
        ]);
        $this->getJson($url);
    }

    /**
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchByName()
    {
        $name = $this->faker->name;
        $mock = Mockery::mock('alias:' . Contact::class);
        $mock->shouldReceive('filter')
            ->once()
            ->withArgs(function ($options) use ($name) {
                return $options === ['term' => $name];
            });

        $url = action('Contacts\ContactsController@search', [
            'term' => $name,
        ]);
        $this->getJson($url);
    }

    public function testGetPerson()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ]);
        $person  = $contact->person;

        $personFields  = ['first_name', 'last_name', 'job_title', 'direct_phone', 'mobile_phone'];
        $companyFields = ['legal_name', 'trading_name', 'abn', 'website', 'default_payment_terms_days'];
        $contactFields = [
            'contact_type',
            'contact_category_id',
            'email',
            'business_phone',
        ];

        $url = action('Contacts\ContactsController@show', ['contact_id' => $person->contact_id]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();

        static::assertArrayHasKey('contact_category', $data);
        static::assertArrayHasKey('contact_status', $data);
        static::assertArrayHasKey('notes', $data);
        static::assertArrayHasKey('addresses', $data);
        static::assertArrayHasKey('tags', $data);
        static::assertArrayHasKey('subsidiaries', $data);
        static::assertArrayHasKey('parent_company', $data);
        static::assertArrayHasKey('managed_accounts', $data);
        static::assertEquals($data['contact_category']['id'], $person->contact->category->id);
        static::assertEquals($data['contact_category']['name'], $person->contact->category->name);
        static::assertEquals($data['contact_status']['id'], $person->contact->latestStatus->id);
        static::assertEquals($data['contact_status']['status'], $person->contact->latestStatus->status);

        foreach ($contactFields as $field) {
            static::assertEquals($data[$field], $person->contact->getAttribute($field));
        }
        foreach ($personFields as $field) {
            static::assertEquals($data[$field], $person->getAttribute($field));
        }
        foreach ($companyFields as $field) {
            static::assertArrayNotHasKey($field, $data);
        }
    }

    public function testGetCompany()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $company = $contact->company;

        $personFields  = ['first_name', 'last_name', 'job_title', 'direct_phone', 'mobile_phone'];
        $companyFields = ['legal_name', 'trading_name', 'abn', 'website', 'default_payment_terms_days'];
        $contactFields = [
            'contact_type',
            'contact_category_id',
            'email',
            'business_phone',
        ];

        $url = action('Contacts\ContactsController@show', ['contact_id' => $company->contact_id]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();

        static::assertArrayHasKey('contact_category', $data);
        static::assertArrayHasKey('contact_status', $data);
        static::assertArrayHasKey('notes', $data);
        static::assertArrayHasKey('addresses', $data);
        static::assertArrayHasKey('tags', $data);
        static::assertArrayHasKey('subsidiaries', $data);
        static::assertArrayHasKey('parent_company', $data);
        static::assertArrayHasKey('managed_accounts', $data);
        static::assertArrayHasKey('avatar_url', $data);
        static::assertEquals($data['contact_category']['id'], $company->contact->category->id);
        static::assertEquals($data['contact_category']['name'], $company->contact->category->name);
        static::assertEquals($data['contact_status']['id'], $company->contact->latestStatus->id);
        static::assertEquals($data['contact_status']['status'], $company->contact->latestStatus->status);

        foreach ($contactFields as $field) {
            static::assertEquals($data[$field], $company->contact->getAttribute($field));
        }
        foreach ($companyFields as $field) {
            static::assertEquals($data[$field], $company->getAttribute($field));
        }
        foreach ($personFields as $field) {
            static::assertArrayNotHasKey($field, $data);
        }
    }

    public function testGetOneRecordFail404()
    {
        $url = action('Contacts\ContactsController@show', ['contact_id' => $this->faker->randomNumber()]);

        $response = $this->getJson($url);
        $response->assertStatus(404);
    }

    public function testUpdatePerson()
    {
        Event::fake(ContactModelChanged::class);

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ]);
        $person  = $contact->person;

        $url  = action('Contacts\ContactsController@update', ['contact_id' => $person->contact_id]);
        $data = [
            'email'          => $this->faker->email,
            'business_phone' => $this->faker->phoneNumber,
            'first_name'     => $this->faker->firstName,
            'last_name'      => $this->faker->lastName,
            'job_title'      => $this->faker->jobTitle,
            'direct_phone'   => $this->faker->phoneNumber,
            'mobile_phone'   => $this->faker->phoneNumber,
        ];

        $response = $this->patchJson($url, $data);

        $response->assertStatus(200)
            ->assertSeeData();

        $reloaded = Contact::find($person->contact_id);

        self::assertEquals($reloaded->email, $data['email']);
        self::assertEquals($reloaded->business_phone, $data['business_phone']);
        self::assertEquals($reloaded->person->first_name, $data['first_name']);
        self::assertEquals($reloaded->person->last_name, $data['last_name']);
        self::assertEquals($reloaded->person->job_title, $data['job_title']);
        self::assertEquals($reloaded->person->direct_phone, $data['direct_phone']);
        self::assertEquals($reloaded->person->mobile_phone, $data['mobile_phone']);

        Event::dispatched(ContactModelChanged::class, function ($e) use ($person) {
            self::assertTrue($e->targetId === $person->contact_id);
        });
    }

    public function testUpdateCompany()
    {
        Event::fake([ContactModelChanged::class]);

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $company = $contact->company;

        $url  = action('Contacts\ContactsController@update', ['contact_id' => $company->contact_id]);
        $data = [
            'email'                      => $this->faker->email,
            'business_phone'             => $this->faker->phoneNumber,
            'legal_name'                 => $this->faker->word,
            'trading_name'               => $this->faker->word,
            'abn'                        => $this->faker->word,
            'website'                    => $this->faker->url,
            'default_payment_terms_days' => $this->faker->numberBetween(1, 30),
        ];

        $response = $this->patchJson($url, $data);

        $response->assertStatus(200)
            ->assertSeeData();

        $reloaded = Contact::find($company->contact_id);
        self::assertEquals($reloaded->email, $data['email']);
        self::assertEquals($reloaded->business_phone, $data['business_phone']);
        self::assertEquals($reloaded->company->legal_name, $data['legal_name']);
        self::assertEquals($reloaded->company->trading_name, $data['trading_name']);
        self::assertEquals($reloaded->company->abn, $data['abn']);
        self::assertEquals($reloaded->company->website, $data['website']);
        self::assertEquals($reloaded->company->default_payment_terms_days, $data['default_payment_terms_days']);

        Event::dispatched(ContactModelChanged::class, function ($e) use ($company) {
            self::assertTrue($e->targetId === $company->contact_id);
        });
    }

    public function testUpdateValidationFail()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ]);
        $person  = $contact->person;
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $company = $contact->company;
        $data    = ['email' => $this->faker->word,];

        $url = action('Contacts\ContactsController@update', ['contact_id' => $person->contact_id]);

        $response = $this->patchJson($url, $data);
        $response->assertStatus(422);

        $url = action('Contacts\ContactsController@update', ['contact_id' => $company->contact_id]);

        $response = $this->patchJson($url, $data);

        $response->assertStatus(422);
    }

    public function testDeleteSuccess()
    {
        /** @var Contact $contact */
        $contact  = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ]);
        $person   = $contact->person;
        $url      = action('Contacts\ContactsController@update', ['contact_id' => $person->contact_id]);
        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(Contact::find($person->contact_id));
    }

    public function testDeleteFail404()
    {
        /** @var ContactPersonProfile $person */
        $url      = action('Contacts\ContactsController@update', ['contact_id' => $this->faker->randomNumber()]);
        $response = $this->deleteJson($url);
        $response->assertStatus(404);
    }

    public function testDeleteFailIfHasSubsidiaries()
    {
        /** @var Contact $parentContact */
        $parentContact = factory(Contact::class)->create();
        /** @var Contact $childContact */
        $childContact = factory(Contact::class)->create();

        $parentContact->subsidiaries()->attach($childContact);

        $url = action('Contacts\ContactsController@destroy', ['contact_id' => $parentContact->id]);

        $response = $this->deleteJson($url);
        $response->assertStatus(405);
    }

    public function testDeleteFailIfAssignedToJob()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        /** @var Job $job */
        $job = factory(Job::class)->create();
        /** @var JobContactAssignmentType $type */
        $type = factory(JobContactAssignmentType::class)->create();

        $job->assignedContacts()->attach($contact, [
            'job_assignment_type_id' => $type->id,
            'invoice_to'             => $this->faker->boolean,
        ]);

        $url = action('Contacts\ContactsController@destroy', ['contact_id' => $contact->id]);

        $response = $this->deleteJson($url);
        $response->assertStatus(405);
    }

    public function testLinkCompanyToCompany()
    {
        /** @var Contact $contact */
        $contact      = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $childCompany = $contact->company;
        /** @var Contact $contact */
        $contact       = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $parentCompany = $contact->company;

        $url = action('Contacts\ContactsController@linkContact', [
            'parent_id' => $parentCompany->contact_id,
            'child_id'  => $childCompany->contact_id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($parentCompany->contact_id);
        self::assertCount(1, $reloaded->subsidiaries);
        self::assertEquals($reloaded->subsidiaries[0]->id, $childCompany->contact_id);
    }

    public function testLinkPersonToCompany()
    {
        /** @var Contact $contact */
        $contact     = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $childPerson = $contact->company;
        /** @var Contact $contact */
        $contact       = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $parentCompany = $contact->company;

        $url = action('Contacts\ContactsController@linkContact', [
            'parent_id' => $parentCompany->contact_id,
            'child_id'  => $childPerson->contact_id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($parentCompany->contact_id);
        self::assertCount(1, $reloaded->subsidiaries);
        self::assertEquals($reloaded->subsidiaries[0]->id, $childPerson->contact_id);
    }

    public function testLinkCompanyToPersonFail()
    {
        /** @var Contact $contact */
        $contact      = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ]);
        $parentPerson = $contact->person;
        /** @var Contact $contact */
        $contact      = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $childCompany = $contact->company;

        $url = action('Contacts\ContactsController@linkContact', [
            'parent_id' => $parentPerson->contact_id,
            'child_id'  => $childCompany->contact_id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(405);
    }

    public function testLinkCompaniesTwiceSuccess()
    {
        /** @var Contact $contact */
        $contact      = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $childCompany = $contact->company;
        /** @var Contact $contact */
        $contact       = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $parentCompany = $contact->company;

        $parentCompany->contact->subsidiaries()->attach($childCompany->contact);
        $url = action('Contacts\ContactsController@linkContact', [
            'parent_id' => $parentCompany->contact_id,
            'child_id'  => $childCompany->contact_id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);
    }

    public function testLinkAlreadyLinkedSuccess()
    {
        /** @var ContactPersonProfile $childPerson */
        $childPerson = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ])->person;
        /** @var ContactCompanyProfile $childCompany */
        $childCompany = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ])->company;
        /** @var ContactCompanyProfile $parentCompany */
        $parentCompany = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ])->company;

        $childCompany->contact->subsidiaries()->attach($childPerson->contact);
        $url = action('Contacts\ContactsController@linkContact', [
            'parent_id' => $parentCompany->contact_id,
            'child_id'  => $childPerson->contact_id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);
    }

    public function testUnlinkContactSuccess()
    {
        /** @var ContactCompanyProfile $childCompany */
        $childCompany = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ])->company;
        /** @var ContactCompanyProfile $parentCompany */
        $parentCompany = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ])->company;

        $parentCompany->contact->subsidiaries()->attach($childCompany->contact);
        $url = action('Contacts\ContactsController@unlinkContact', [
            'parent_id' => $parentCompany->contact_id,
            'child_id'  => $childCompany->contact_id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($parentCompany->contact_id);
        self::assertCount(0, $reloaded->subsidiaries);
    }

    public function testAddPersonSuccess()
    {
        $contactCategory = factory(ContactCategory::class)->create();

        $data = [
            'contact_category_id' => $contactCategory->id,
            'business_phone'      => $this->faker->phoneNumber,
            'email'               => $this->faker->email,
            'first_name'          => $this->faker->firstName,
            'last_name'           => $this->faker->lastName,
            'job_title'           => $this->faker->jobTitle,
            'direct_phone'        => $this->faker->phoneNumber,
            'mobile_phone'        => $this->faker->phoneNumber,
        ];

        $url = action('Contacts\ContactsController@addPerson', $data);

        $response = $this->postJson($url);
        $response->assertStatus(201)->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = Contact::find($responseData['id']);

        self::assertNotNull($reloaded);
        $attributes = array_merge(
            $reloaded->getAttributes(),
            $reloaded->person->getAttributes()
        );

        foreach ($data as $key => $value) {
            self::assertEquals($value, $attributes[$key]);
        }
    }

    public function testAddPersonValidationFail()
    {
        $data = [
            'job_title' => $this->faker->jobTitle,
        ];

        $url = action('Contacts\ContactsController@addPerson', $data);

        $response = $this->postJson($url);
        $response->assertStatus(422);

        $data = $response->json();
        self::assertNotNull($data);
        self::assertArrayHasKey('fields', $data);
        self::assertArrayHasKey('contact_category_id', $data['fields']);
        self::assertArrayHasKey('first_name', $data['fields']);
        self::assertArrayHasKey('last_name', $data['fields']);
        self::assertArrayHasKey('direct_phone', $data['fields']);
        self::assertArrayHasKey('mobile_phone', $data['fields']);
    }

    public function testAddCompanySuccess()
    {
        $contactCategory = factory(ContactCategory::class)->create();
        $data            = [
            'contact_category_id'        => $contactCategory->id,
            'business_phone'             => $this->faker->phoneNumber,
            'email'                      => $this->faker->email,
            'legal_name'                 => $this->faker->firstName,
            'trading_name'               => $this->faker->lastName,
            'abn'                        => $this->faker->jobTitle,
            'website'                    => $this->faker->phoneNumber,
            'default_payment_terms_days' => $this->faker->numberBetween(1, 30),
        ];

        $url = action('Contacts\ContactsController@addCompany', $data);

        $response = $this->postJson($url);
        $response->assertStatus(201)->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = Contact::find($responseData['id']);

        self::assertNotNull($reloaded);
        $attributes = array_merge(
            $reloaded->getAttributes(),
            $reloaded->company->getAttributes()
        );

        foreach ($data as $key => $value) {
            self::assertEquals($value, $attributes[$key]);
        }
    }

    public function testAddCompanyValidationFail()
    {
        $data = [
            'business_phone' => $this->faker->phoneNumber,
            'email'          => $this->faker->email,
            'trading_name'   => $this->faker->lastName,
            'website'        => $this->faker->phoneNumber,
        ];

        $url = action('Contacts\ContactsController@addCompany', $data);

        $response = $this->postJson($url);
        $response->assertStatus(422);

        $data = $response->json();
        self::assertNotNull($data);
        self::assertArrayHasKey('fields', $data);
        self::assertArrayHasKey('contact_category_id', $data['fields']);
        self::assertArrayHasKey('legal_name', $data['fields']);
        self::assertArrayHasKey('abn', $data['fields']);
        self::assertArrayHasKey('default_payment_terms_days', $data['fields']);
    }

    public function testCreateNewAvatarSuccess()
    {
        $contact = factory(Contact::class)->create();

        $file     = $this->getFakeImage();
        $url      = action('Contacts\ContactsController@updateAvatar', [
            'contact_id' => $contact->id,
        ]);
        $response = $this->postJson($url, ['file' => $file]);

        $data = $response->assertStatus(200)->assertSeeData()->getData();
        app()->make(ContactsServiceInterface::class)->deleteContactAvatar($contact->id);

        self::assertArrayHasKey('avatar', $data);

        $avatar = $data['avatar'];
        self::assertArrayHasKey('file_name', $avatar);
        self::assertArrayHasKey('file_size', $avatar);
        self::assertArrayHasKey('mime_type', $avatar);
        self::assertArrayHasKey('width', $avatar);
        self::assertArrayHasKey('height', $avatar);
        self::assertArrayHasKey('original_photo_id', $avatar);
        self::assertArrayHasKey('created_at', $avatar);
        self::assertArrayHasKey('updated_at', $avatar);
        self::assertArrayHasKey('url', $avatar);
        self::assertArrayNotHasKey('storage_uid', $avatar);
        self::assertEquals($file->getClientOriginalName(), $avatar['file_name']);
        self::assertEquals($file->getMimeType(), $avatar['mime_type']);
        self::assertEquals($file->getSize(), $avatar['file_size']);
    }

    public function testUpdateExistingAvatarSuccess()
    {
        $contact = factory(Contact::class)->create();
        $url     = action('Contacts\ContactsController@updateAvatar', [
            'contact_id' => $contact->id,
        ]);
        $this->postJson($url, ['file' => $this->getFakeImage()])->assertStatus(200);

        $file     = $this->getFakeImage();
        $response = $this->postJson($url, ['file' => $file]);
        $data     = $response->assertStatus(200)->assertSeeData()->getData();
        app()->make(ContactsServiceInterface::class)->deleteContactAvatar($contact->id);

        self::assertArrayHasKey('avatar', $data);

        $avatar = $data['avatar'];
        self::assertArrayHasKey('file_name', $avatar);
        self::assertArrayHasKey('file_size', $avatar);
        self::assertArrayHasKey('mime_type', $avatar);
        self::assertArrayHasKey('width', $avatar);
        self::assertArrayHasKey('height', $avatar);
        self::assertArrayHasKey('original_photo_id', $avatar);
        self::assertArrayHasKey('created_at', $avatar);
        self::assertArrayHasKey('updated_at', $avatar);
        self::assertArrayHasKey('url', $avatar);
        self::assertArrayNotHasKey('storage_uid', $avatar);
        self::assertEquals($file->getClientOriginalName(), $avatar['file_name']);
        self::assertEquals($file->getMimeType(), $avatar['mime_type']);
        self::assertEquals($file->getSize(), $avatar['file_size']);
    }

    public function testDeleteAvatarSuccess()
    {
        $contact = factory(Contact::class)->create();
        $url     = action('Contacts\ContactsController@updateAvatar', [
            'contact_id' => $contact->id,
        ]);
        $this->postJson($url, ['file' => $this->getFakeImage()])->assertStatus(200);

        $url = action('Contacts\ContactsController@deleteAvatar', [
            'contact_id' => $contact->id,
        ]);
        $this->deleteJson($url)->assertStatus(200);
    }

    public function testDeleteAvatarFail()
    {
        $contact = factory(Contact::class)->create();
        $url     = action('Contacts\ContactsController@deleteAvatar', [
            'contact_id' => $contact->id,
        ]);
        $this->deleteJson($url)->assertStatus(405);
    }

    /**
     * @param string|null $fileName
     *
     * @return \Illuminate\Http\Testing\File
     */
    private function getFakeImage(string $fileName = null)
    {
        if (!$fileName) {
            $fileName = $this->faker->word . $this->faker->randomElement(['.png', '.jpg']);
        }

        return UploadedFile::fake()->image($fileName);
    }
}
