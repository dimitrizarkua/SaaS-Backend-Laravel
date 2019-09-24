<?php

namespace App\Http\Responses\Contacts;

use App\Components\Contacts\Resources\ContactNoteResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactNoteResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactNoteResponse extends ApiOKResponse
{
    protected $resource = ContactNoteResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/ContactNoteResource")
     * @var \App\Components\Contacts\Resources\ContactNoteResource
     */
    protected $data;
}
