<?php

namespace App\Http\Responses\Contacts;

use App\Http\Responses\ApiOKResponse;

/**
 * Class ContactStatusListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class ContactStatusListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ContactStatuses")
     * ),
     */
    protected $data;
}
