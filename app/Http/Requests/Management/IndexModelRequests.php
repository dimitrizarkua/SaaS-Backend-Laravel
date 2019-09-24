<?php

namespace App\Http\Requests\Management;

use App\Enums\ElasticIndexableModels;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class IndexModelRequests
 *
 * @package App\Http\Requests\Management
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="models",
 *         description="A list of models to be indexed/flushed",
 *         type="array",
 *         @OA\Items(
 *              ref="#/components/schemas/ElasticIndexableModels"
 *         )
 *     ),
 * )
 */
class IndexModelRequests extends ApiRequest
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
            'models'   => 'array',
            'models.*' => ['required', Rule::in(array_keys(ElasticIndexableModels::values()))],
        ];
    }

    /**
     * @return string[]
     */
    public function getModelClassList(): array
    {
        $input = $this->input('models');

        if (empty($input)) {
            $input = array_keys(ElasticIndexableModels::values());
        }

        $output = [];
        foreach ($input as $model) {
            $output[] = ElasticIndexableModels::value($model);
        }

        return $output;
    }
}
