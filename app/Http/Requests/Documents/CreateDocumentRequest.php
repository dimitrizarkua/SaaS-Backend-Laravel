<?php

namespace App\Http\Requests\Documents;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateDocumentRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"file"},
 *     @OA\Property(
 *          property="file",
 *          type="string",
 *          format="binary",
 *     ),
 * )
 *
 * @package App\Http\Requests\Documents
 */
class CreateDocumentRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file',

            // TODO: Not validating size and mime-types for now, ask whether we need to validate these params too
            //'file' => 'required|mimes:doc,pdf,docx,zip|max:2048',
        ];
    }
}
