<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;
use App\Rules\CountryName;
use OpenApi\Annotations as OA;

/**
 * Class CreateCountryRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *         property="name",
 *         description="Country name",
 *         type="string",
 *         example="Australia"
 *     )
 * )
 *
 * @package App\Http\Requests\Addresses
 */
class CreateCountryRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:countries', new CountryName()],
        ];
    }

    /**
     * Returns name of country.
     *
     * @return string
     */
    public function getCountryName(): string
    {
        return $this->input('name');
    }
}
