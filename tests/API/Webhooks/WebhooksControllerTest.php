<?php

namespace Tests\API\Webhooks;

use App\Core\JsonModel;
use App\Jobs\Messages\HandleIncomingJobMessage;
use Illuminate\Support\Facades\Queue;
use Tests\API\ApiTestCase;

/**
 * Class WebhooksControllerTest
 *
 * @package Tests\API\Webhooks
 * @group   webhooks
 * @group   api
 */
class WebhooksControllerTest extends ApiTestCase
{
    public function testIncomingJobMessageHandling()
    {
        $data = [
            'timestamp'          => (int)$this->faker->dateTime()->format('U'),
            'token'              => $this->faker->sha1,
            'signature'          => $this->faker->sha256,
            'domain'             => $this->faker->domainName,
            'subject'            => $this->faker->sentence,
            'sender'             => $this->faker->email,
            'from'               => $this->faker->email,
            'body-plain'         => $this->faker->paragraph,
            'body-html'          => $this->faker->randomHtml(),
            'stripped-text'      => $this->faker->paragraph,
            'stripped-signature' => $this->faker->optional()->sentence,
            'stripped-html'      => $this->faker->optional()->randomHtml(),
        ];

        Queue::fake();

        $url = action('WebhooksController@handleMailgunIncomingJobMessageWebhook');
        $response = $this->postJson($url, $data, ['Content-Type' => 'application/x-www-form-urlencoded']);

        $response->assertStatus(200);

        $content = JsonModel::replaceHyphensWithUnderscores(\GuzzleHttp\json_encode($data));
        $content = \GuzzleHttp\json_decode($content, true);

        Queue::assertPushedOn(
            'messages',
            HandleIncomingJobMessage::class,
            function (HandleIncomingJobMessage $job) use ($content) {
                return empty(array_diff_assoc($job->getData(), $content));
            }
        );
    }
}
