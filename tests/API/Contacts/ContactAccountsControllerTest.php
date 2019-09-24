<?php

namespace Tests\API\Contacts;

use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ManagedAccount;
use App\Components\Tags\Enums\SpecialTags;
use App\Components\Tags\Enums\TagTypes;
use App\Models\User;
use Tests\API\ApiTestCase;

/**
 * Class ContactAccountsControllerTest
 *
 * @package Tests\API\Contacts
 * @group   contacts
 */
class ContactAccountsControllerTest extends ApiTestCase
{
    protected $permissions = ['contacts.view', 'contacts.update'];

    /** @var ContactsServiceInterface */
    private   $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = app()->make(ContactsServiceInterface::class);
    }

    public function testCreateAccount()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();

        $url = action('Contacts\ContactAccountsController@addManagedAccount', [
            'contact_id' => $contact->id,
            'user_id'    => $this->user->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertCount(1, $reloaded->managedAccounts);

        $url  = action('Contacts\ContactsController@show', ['contact_id' => $contact->id]);
        $data = $this->getJson($url)->assertStatus(200)->getData();
        self::assertCount(1, $data['managed_accounts']);
    }

    public function testDeleteAccount()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $account = factory(ManagedAccount::class)->create([
            'contact_id' => $contact->id,
        ]);

        $url = action('Contacts\ContactAccountsController@deleteManagedAccount', [
            'contact_id' => $contact->id,
            'user_id'    => $account->user_id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertCount(0, $reloaded->managedAccounts);
    }

    public function testAddManageAccountTag()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $user    = factory(User::class)->create();

        $url = action('Contacts\ContactAccountsController@addManagedAccount', [
            'contact_id' => $contact->id,
            'user_id'    => $user->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        $tag      = $reloaded->tags()->first();
        self::assertNotNull($tag);
        self::assertEquals(SpecialTags::MANAGED_ACCOUNT, $tag->name);
        self::assertEquals(TagTypes::CONTACT, $tag->type);
    }

    public function testDeleteManageAccountTag()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $count   = $this->faker->numberBetween(1, 3);
        $users   = factory(User::class, $count)->create();

        foreach ($users as $user) {
            $this->service->addManagedAccount($contact->id, $user->id);
        }

        foreach ($users as $user) {
            $url = action('Contacts\ContactAccountsController@deleteManagedAccount', [
                'contact_id' => $contact->id,
                'user_id'    => $user->id,
            ]);

            $response = $this->deleteJson($url);
            $response->assertStatus(200);

            $reloaded = Contact::find($contact->id);
            self::assertEquals(
                $reloaded->managedAccounts()->exists(),
                $reloaded->tags()->exists()
            );
        }
    }
}
