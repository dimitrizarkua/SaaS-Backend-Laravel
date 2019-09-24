<?php

namespace App\Http\Requests\RBAC;

use App\Http\Requests\ApiRequest;
use App\Models\VO\UpdateUserData;
use App\Rules\FirstName;
use App\Rules\LastName;
use JsonMapper_Exception;

/**
 * Class UpdateUserRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="first_name",
 *          description="First name",
 *          type="string",
 *          example="John"
 *     ),
 *     @OA\Property(
 *          property="last_name",
 *          description="Last name",
 *          type="string",
 *          example="Smith"
 *     ),
 *     @OA\Property(
 *          property="password",
 *          description="Password",
 *          type="string",
 *          example="UserPassword"
 *     ),
 *     @OA\Property(
 *          property="invoice_approve_limit",
 *          description="Max amount allowing user to approve invoices",
 *          type="number",
 *          example="1.2"
 *     ),
 *     @OA\Property(
 *          property="purchase_order_approve_limit",
 *          description="Max amount allowing user to approve purchase orders",
 *          type="number",
 *          example="1.2"
 *     ),
 *     @OA\Property(
 *          property="credit_note_approval_limit",
 *          description="Max amount allowing user to approve credit notes",
 *          type="number",
 *          example="1.2"
 *     ),
 *     @OA\Property(
 *          property="working_hours_per_week",
 *          description="Working hours per week",
 *          type="number",
 *          format="float",
 *          example=40
 *     ),
 *     @OA\Property(
 *          property="primary_location_id",
 *          type="integer",
 *          description="Primary location identifier",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="locations",
 *          type="array",
 *          @OA\Items(
 *              type="integer",
 *              description="Location identifier",
 *              example=1
 *          ),
 *     ),
 *     @OA\Property(
 *         property="contact_id",
 *         type="integer",
 *         description="User's contact identifier",
 *         example=1,
 *         nullable=true,
 *     ),
 * )
 *
 * @package App\Http\Requests\RBAC
 */
class UpdateUserRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name'                   => [new FirstName()],
            'last_name'                    => [new LastName()],
            'password'                     => 'string|min:6',
            'invoice_approve_limit'        => 'numeric|between:0,99999999.99',
            'purchase_order_approve_limit' => 'numeric|between:0,99999999.99',
            'credit_note_approval_limit'   => 'numeric|between:0,99999999.99',
            'working_hours_per_week'       => 'numeric|between:0,168',
            'primary_location_id'          => 'integer|exists:locations,id',
            'locations'                    => 'array|nullable',
            'locations.*'                  => 'integer|exists:locations,id',
            'contact_id'                   => 'nullable|integer|exists:contacts,id',
        ];
    }

    /**
     * @return UpdateUserData
     * @throws JsonMapper_Exception
     */
    public function getUpdateUserData(): UpdateUserData
    {
        return new UpdateUserData($this->validated());
    }
}
