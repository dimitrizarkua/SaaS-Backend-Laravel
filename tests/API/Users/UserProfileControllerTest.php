<?php

namespace Tests\API\Users;

use App\Components\Locations\Models\Location;
use App\Components\Teams\Models\Team;
use App\Components\Users\Interfaces\UserProfileServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Tests\API\ApiTestCase;

/**
 * Class UserProfileControllerTest
 *
 * @package Tests\API\Users
 * @group   users
 * @group   api
 */
class UserProfileControllerTest extends ApiTestCase
{
    public function testGetProfileSuccess()
    {
        $url = action('Users\UserProfileController@getProfile');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();

        self::assertEquals($this->user->id, $data['id']);
        self::assertEquals($this->user->first_name, $data['first_name']);
        self::assertEquals($this->user->last_name, $data['last_name']);
        self::assertEquals($this->user->email, $data['email']);
        self::assertEquals($this->user->full_name, $data['full_name']);
        self::assertEquals($this->user->created_at, Carbon::make($data['created_at']));
        self::assertEquals($this->user->updated_at, Carbon::make($data['updated_at']));
    }

    public function testUpdateProfileSuccess()
    {
        $request = [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
        ];

        $url = action('Users\UserProfileController@updateProfile');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $request);

        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();

        self::assertEquals($request['first_name'], $data['first_name']);
        self::assertEquals($request['last_name'], $data['last_name']);
    }

    public function testGetLocationsSuccess()
    {
        $locations = factory(Location::class, $this->faker->numberBetween(1, 5))->create();
        $this->user->locations()->attach($locations);

        $url = action('Users\UserProfileController@getLocations');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData();

        self::assertCount(count($locations), $response->getData());
    }

    public function testGetTeamsSuccess()
    {
        $teams = factory(Team::class, $this->faker->numberBetween(1, 5))->create();
        $this->user->teams()->attach($teams);

        $url = action('Users\UserProfileController@getTeams');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData();

        self::assertCount(count($teams), $response->getData());
    }

    public function testCreateNewAvatarSuccess()
    {
        $file     = $this->getFakeImage();
        $url      = action('Users\UserProfileController@updateAvatar');
        $response = $this->postJson($url, ['file' => $file]);

        $data = $response->assertStatus(200)->assertSeeData()->getData();
        app()->make(UserProfileServiceInterface::class)->deleteAvatar($this->user->id);

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
        $url = action('Users\UserProfileController@updateAvatar');
        $this->postJson($url, ['file' => $this->getFakeImage()])->assertStatus(200);

        $file     = $this->getFakeImage();
        $response = $this->postJson($url, ['file' => $file]);
        $data     = $response->assertStatus(200)->assertSeeData()->getData();
        app()->make(UserProfileServiceInterface::class)->deleteAvatar($this->user->id);

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
        $url = action('Users\UserProfileController@updateAvatar');
        $this->postJson($url, ['file' => $this->getFakeImage()])->assertStatus(200);

        $url = action('Users\UserProfileController@deleteAvatar');
        $this->deleteJson($url)->assertStatus(200);
    }

    public function testDeleteAvatarFail()
    {
        $url = action('Users\UserProfileController@deleteAvatar');
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
