<?php

namespace App\Http\Requests\Photos;

use App\Http\Requests\ApiRequest;
use Illuminate\Http\UploadedFile;

/**
 * Class CreatePhotoRequest
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
 * @package App\Http\Requests\Photos
 */
class CreatePhotoRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,jpeg,png',
        ];
    }

    /**
     * @return \Illuminate\Http\UploadedFile
     */
    public function photo(): UploadedFile
    {
        return $this->file('file');
    }
}
