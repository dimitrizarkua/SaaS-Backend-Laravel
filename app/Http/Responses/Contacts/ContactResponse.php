<?php

namespace App\Http\Responses\Contacts;

use App\Components\Contacts\Resources\ContactResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactResponse extends ApiOKResponse
{
    protected $resource = ContactResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/ContactResource")
     * @var \App\Components\Contacts\Resources\ContactResource
     */
    protected $data;
}
