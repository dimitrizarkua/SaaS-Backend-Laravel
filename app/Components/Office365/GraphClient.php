<?php

namespace App\Components\Office365;

use App\Components\Office365\Models\UserResource;
use GuzzleHttp\Client as HttpClient;

/**
 * Class GraphClient
 *
 * @package App\Components\Office365
 */
class GraphClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $apiClient;

    /**
     * GraphClient constructor.
     */
    public function __construct()
    {
        $this->apiClient = new HttpClient([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
        ]);
    }

    /**
     * Returns user resource from Microsoft Graph API by its access token.
     *
     * @param string $accessToken
     *
     * @return \App\Components\Office365\Models\UserResource|object
     * @throws \JsonMapper_Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getUser(string $accessToken): UserResource
    {
        $response = $this->apiClient->get('me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return UserResource::createFromJson($data);
    }
}
