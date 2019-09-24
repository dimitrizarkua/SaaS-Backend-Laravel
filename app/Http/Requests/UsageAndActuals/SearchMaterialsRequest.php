<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class SearchMaterialsRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="name",
 *          description="Name of material",
 *          type="string",
 *      ),
 * )
 *
 * @package App\Http\Requests\UsageAndActuals
 */
class SearchMaterialsRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string',
        ];
    }

    /**
     * Returns name from request.
     *
     * @return array
     */
    public function getName()
    {
        return $this->get('name');
    }
}
