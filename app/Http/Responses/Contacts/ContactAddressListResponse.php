<?php

namespace App\Http\Responses\Contacts;

use App\Components\Contacts\Resources\ContactAddressListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactAddressListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactAddressListResponse extends ApiOKResponse
{
    protected $resource = ContactAddressListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ContactAddressListResource")
     * ),
     * @var \App\Components\Contacts\Resources\ContactAddressListResource[]
     */
    protected $data;
}
