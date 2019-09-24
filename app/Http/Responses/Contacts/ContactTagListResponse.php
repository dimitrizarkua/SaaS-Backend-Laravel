<?php

namespace App\Http\Responses\Contacts;

use App\Components\Contacts\Resources\ContactTagListResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class ContactTagListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactTagListResponse extends ApiOKResponse
{
    protected $resource = ContactTagListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Tag")
     * ),
     * @var \App\Components\Tags\Models\Tag[]
     */
    protected $data;
}
