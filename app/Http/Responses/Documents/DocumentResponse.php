<?php

namespace App\Http\Responses\Documents;

use App\Components\Documents\Resources\DocumentResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class DocumentResponse
 *
 * @package App\Http\Responses\Documents
 * @OA\Schema(required={"data"})
 */
class DocumentResponse extends ApiOKResponse
{
    protected $resource = DocumentResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/DocumentResource")
     *
     * @var \App\Components\Documents\Models\Document
     */
    protected $data;
}
