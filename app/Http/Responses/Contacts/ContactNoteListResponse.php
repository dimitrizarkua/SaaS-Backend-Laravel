<?php

namespace App\Http\Responses\Contacts;

use App\Components\Contacts\Resources\ContactNoteListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactNoteListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactNoteListResponse extends ApiOKResponse
{
    protected $resource = ContactNoteListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ContactNoteListResource")
     * ),
     * @var \App\Components\Contacts\Resources\ContactNoteListResource[]
     */
    protected $data;
}
