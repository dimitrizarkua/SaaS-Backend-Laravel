<?php

namespace App\Http\Responses\Contacts;

use App\Http\Responses\ApiOKResponse;

/**
 * Class ContactCategoryListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactCategoryListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ContactCategory")
     * ),
     * @var \App\Components\Contacts\Models\ContactCategory[]
     */
    protected $data;
}
