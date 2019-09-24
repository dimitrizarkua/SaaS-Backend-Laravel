<?php

namespace Tests\API\Auth;

use App\Components\Office365\Facades\GraphClient;
use App\Components\Office365\Models\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Tests\API\ApiTestCase;

/**
 * Class Office365AuthTest
 *
 * @package Tests\API\Auth
 * @group   microsoft
 * @group   api
 */
class Office365AuthTest extends ApiTestCase
{
    public $models = [User::class, Client::class];

    public function testSuccessResponse()
    {
        Client::create([
            'name'                   => $this->faker->word,
            'secret'                 => Hash::make($this->faker->word),
            'redirect'               => 'http://localhost',
            'personal_access_client' => false,
            'revoked'                => false,
            'password_client'        => true,
        ]);

        $fakeToken = $this->faker->word;

        GraphClient::shouldReceive('getUser')
            ->with($fakeToken)
            ->andReturn(UserResource::createFromJson([
                'id'        => $this->faker->uuid,
                'givenName' => $this->faker->firstName,
                'surname'   => $this->faker->lastName,
                'mail'      => $this->faker->word . '@steamatic.com.au',
            ]));

        $url = url('/oauth/token');

        $response = $this->json('post', $url, [
            'grant_type'   => 'social',
            'network'      => 'office365',
            'access_token' => $fakeToken,
        ]);

        $response->assertStatus(200)
            ->assertSee('access_token')
            ->assertSee('refresh_token')
            ->assertSee('token_type')
            ->assertSee('expires_in');
        self::assertEqualsWithDelta(1500, json_decode($response->getContent())->expires_in, 10);
    }

    public function testSuccessMobileResponse()
    {
        Client::create([
            'name'                   => $this->faker->word,
            'secret'                 => Hash::make($this->faker->word),
            'redirect'               => 'http://localhost',
            'personal_access_client' => false,
            'revoked'                => false,
            'password_client'        => true,
        ]);

        $fakeToken = $this->faker->word;

        GraphClient::shouldReceive('getUser')
            ->with($fakeToken)
            ->andReturn(UserResource::createFromJson([
                'id'        => $this->faker->uuid,
                'givenName' => $this->faker->firstName,
                'surname'   => $this->faker->lastName,
                'mail'      => $this->faker->word . '@steamatic.com.au',
            ]));

        $url = url('/oauth/token');

        $response = $this->json('post', $url, [
            'grant_type'   => 'social_mobile',
            'network'      => 'office365',
            'access_token' => $fakeToken,
        ]);

        $response->assertStatus(200)
            ->assertSee('access_token')
            ->assertSee('refresh_token')
            ->assertSee('token_type')
            ->assertSee('expires_in');
        self::assertEqualsWithDelta(
            (now()->addYear()->diffInDays()) * 24 * 3600,
            json_decode($response->getContent())->expires_in,
            10
        );
    }

    public function testNotAllowedResponseWithInvalidEmail()
    {
        Client::create([
            'name'                   => $this->faker->word,
            'secret'                 => Hash::make($this->faker->word),
            'redirect'               => 'http://localhost',
            'personal_access_client' => false,
            'revoked'                => false,
            'password_client'        => true,
        ]);

        $fakeToken = $this->faker->word;

        GraphClient::shouldReceive('getUser')
            ->with($fakeToken)
            ->andReturn(UserResource::createFromJson([
                'id'        => $this->faker->uuid,
                'givenName' => $this->faker->firstName,
                'surname'   => $this->faker->lastName,
                'mail'      => $this->faker->word . '@not-allowed-domain.com',
            ]));

        $url = url('/oauth/token');

        $response = $this->postJson($url, [
            'grant_type'   => 'social',
            'network'      => 'office365',
            'access_token' => $fakeToken,
        ]);

        $response->assertStatus(405)
            ->assertSee('Email address belongs to forbidden domain');
    }

    public function testNotAllowedResponseWithInvalidAccessToken()
    {
        Client::create([
            'name'                   => $this->faker->word,
            'secret'                 => Hash::make($this->faker->word),
            'redirect'               => 'http://localhost',
            'personal_access_client' => false,
            'revoked'                => false,
            'password_client'        => true,
        ]);

        $fakeToken = $this->faker->word;

        $url = url('/oauth/token');

        $response = $this->postJson($url, [
            'grant_type'   => 'social',
            'network'      => 'office365',
            'access_token' => $fakeToken,
        ]);

        $response->assertStatus(405)
            ->assertSee('Unable to retrieve user data from provider');
    }

    public function testShouldLogInWithPasswordAfterLinkageWithMsAccount()
    {
        //Create fake APP client
        Client::create([
            'name'                   => $this->faker->word,
            'secret'                 => Hash::make($this->faker->word),
            'redirect'               => 'http://localhost',
            'personal_access_client' => false,
            'revoked'                => false,
            'password_client'        => true,
        ]);

        $email    = $this->faker->word . '@steamatic.com.au';
        $password = $this->faker->password;
        /** @var User $user */
        $user = factory(User::class)->create([
            'email' => $email,
        ]);

        $user->setPassword($password);
        $user->save();

        //1 Test that user is able to login with password
        $url = url('/oauth/token');

        $data = [
            'grant_type' => 'password',
            'username'   => $email,
            'password'   => $password,
        ];

        $this->postJson($url, $data)
            ->assertStatus(200)
            ->assertSee('token_type')
            ->assertSee('access_token')
            ->assertSee('refresh_token')
            ->assertSee('token_type')
            ->assertSee('expires_in');

        //2 Try to authorize with Office365
        $fakeToken = $this->faker->word;

        $fakeUser = UserResource::createFromJson([
            'id'        => $this->faker->uuid,
            'givenName' => $user->first_name,
            'surname'   => $user->last_name,
            'mail'      => $this->faker->word . '@steamatic.com.au',
        ]);
        GraphClient::shouldReceive('getUser')
            ->with($fakeToken)
            ->andReturn($fakeUser);

        $url = url('/oauth/token');

        $data = [
            'grant_type'   => 'social',
            'network'      => 'office365',
            'access_token' => $fakeToken,
        ];

        $this->postJson($url, $data)
            ->assertSee('access_token')
            ->assertSee('refresh_token')
            ->assertSee('token_type')
            ->assertSee('expires_in');

        //3 Try to login with password for the second time
        $url = url('/oauth/token');

        $data = [
            'grant_type' => 'password',
            'username'   => $email,
            'password'   => $password,
        ];

        $this->postJson($url, $data)
            ->assertStatus(200)
            ->assertSee('token_type')
            ->assertSee('access_token')
            ->assertSee('refresh_token')
            ->assertSee('token_type')
            ->assertSee('expires_in');
    }
}
