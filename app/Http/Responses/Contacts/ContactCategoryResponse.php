<?php

namespace App\Http\Responses\Contacts;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactCategoryResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactCategoryResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/ContactCategory")
     * @var \App\Components\Contacts\Models\ContactCategory
     */
    protected $data;
}
