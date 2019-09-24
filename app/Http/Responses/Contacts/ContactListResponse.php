<?php

namespace App\Http\Responses\Contacts;

use App\Components\Contacts\Resources\ContactListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactListResponse extends ApiOKResponse
{
    protected $resource = ContactListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ContactListResource")
     * ),
     * @var \App\Components\Contacts\Resources\ContactListResource[]
     */
    protected $data;
}
