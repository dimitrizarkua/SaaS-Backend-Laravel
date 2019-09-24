<?php

namespace Tests\Unit\Microsoft;

use App\Components\Office365\Events\Office365UserCreated;
use App\Components\Office365\Facades\GraphClient;
use App\Components\Office365\Interfaces\MicrosoftServiceInterface;
use App\Components\Office365\Models\UserResource;
use App\Models\User;
use League\OAuth2\Server\Exception\OAuthServerException;
use Tests\TestCase;

/**
 * Class MicrosoftServiceTest
 *
 * @package Tests\Unit\Microsoft
 * @group   microsoft
 */
class MicrosoftServiceTest extends TestCase
{
    public $models = [User::class];
    /**
     * @var \App\Components\Office365\Interfaces\MicrosoftServiceInterface
     */
    private $service;
    /**
     * @var UserResource
     */
    private $userResource;

    public function setUp()
    {
        parent::setUp();
        $email              = $this->faker->word . '@steamatic.com.au';
        $this->userResource = UserResource::createFromJson([
            'id'        => $this->faker->uuid,
            'givenName' => $this->faker->firstName,
            'surname'   => $this->faker->lastName,
            'mail'      => $email,
        ]);
        $this->service      = $this->app->make(MicrosoftServiceInterface::class);
    }

    public function testShouldCreateNewUser()
    {
        $fakeToken = $this->faker->word;
        $this->registerMock($fakeToken);

        // Verify that user doesn't exists
        $model = User::where('azure_graph_id', $this->userResource->id)->first();
        self::assertNull($model);

        $this->expectsEvents(Office365UserCreated::class);

        //Create user
        $user = $this->service->createOrGetUser($fakeToken);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals($this->userResource->id, $user->azure_graph_id);
        self::assertEquals($this->userResource->getFirstName(), $user->first_name);
        self::assertEquals($this->userResource->getLastName(), $user->last_name);
        self::assertEquals($this->userResource->getEmail(), $user->email);
    }

    public function testShouldLinkToExistingUser()
    {
        $fakeToken = $this->faker->word;
        $this->registerMock($fakeToken);
        $existingUser = factory(User::class)->create([
            'email'      => $this->userResource->getEmail(),
            'first_name' => $this->userResource->getFirstName(),
            'last_name'  => $this->userResource->getLastName(),
        ]);

        //Create user
        $user = $this->service->createOrGetUser($fakeToken);
        self::assertEquals($existingUser->id, $user->id);
        self::assertEquals($this->userResource->id, $user->azure_graph_id);
    }

    public function testShouldReturnExistingUser()
    {
        $existingUser = factory(User::class)->create([
            'email'          => $this->userResource->getEmail(),
            'first_name'     => $this->userResource->getFirstName(),
            'last_name'      => $this->userResource->getLastName(),
            'azure_graph_id' => $this->userResource->id,
        ]);

        $fakeToken = $this->faker->word;
        $this->registerMock($fakeToken);

        $user = $this->service->createOrGetUser($fakeToken);
        self::assertNotNull($user);
        self::assertEquals($existingUser->id, $user->id);
    }

    public function testShouldFailedWithInvalidEmail()
    {
        $this->userResource->mail = $this->faker->word . '@not-allowed-domain.com';
        $fakeToken                = $this->faker->word;
        $this->registerMock($fakeToken);

        $this->expectException(OAuthServerException::class);
        $this->service->createOrGetUser($fakeToken);
    }

    private function registerMock($accessToken): void
    {
        GraphClient::shouldReceive('getUser')
            ->with($accessToken)
            ->andReturn($this->userResource);
    }
}
