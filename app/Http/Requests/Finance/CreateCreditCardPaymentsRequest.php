<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Facades\App;
use LVR\CreditCard\CardCvc;
use LVR\CreditCard\CardNumber;
use LVR\CreditCard\CardExpirationYear;
use LVR\CreditCard\CardExpirationMonth;
use Omnipay\Common\CreditCard;

/**
 * Class CreateCreditCardPaymentsRequest
 *
 * @package App\Http\Requests\Finance
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "name",
 *          "number",
 *          "expiry_month",
 *          "expiry_year",
 *          "email",
 *          "cvv",
 *          "billing_address1",
 *          "billing_city",
 *          "billing_country",
 *     },
 *     @OA\Property(
 *        property="name",
 *        description="Name on card",
 *        type="string",
 *        example="Bobby Tables"
 *     ),
 *     @OA\Property(
 *        property="number",
 *        type="string",
 *        example="4200000000000000"
 *     ),
 *     @OA\Property(
 *        property="cvv",
 *        type="integer",
 *        example=123
 *     ),
 *     @OA\Property(
 *        property="expiry_month",
 *        type="integer",
 *        example=12
 *     ),
 *     @OA\Property(
 *        property="expiry_year",
 *        type="integer",
 *        example=2038
 *     ),
 *     @OA\Property(
 *        property="email",
 *        type="email",
 *        example="testcard@gmail.com"
 *     ),
 *     @OA\Property(
 *        property="billing_address1",
 *        type="string",
 *        example="address"
 *     ),
 *     @OA\Property(
 *        property="billing_city",
 *        type="string",
 *        example="Sydney"
 *     ),
 *     @OA\Property(
 *        property="billing_country",
 *        type="string",
 *        example="Australia"
 *     ),
 * )
 */
class CreateCreditCardPaymentsRequest extends ApiRequest
{
    const TEST_CARD_NUMBERS = ['4200000000000000', '4000000000000002'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        $this->number = preg_replace('/\s+/', '', $this->get('number'));

        $rules = [
            'name'             => 'required|string',
            'number'           => ['required', new CardNumber],
            'cvv'              => ['required', new CardCvc($this->number)],
            'email'            => 'required|email',
            'billing_address1' => 'required|string',
            'billing_city'     => 'required|string',
            'billing_country'  => 'required|string',
        ];

        $rules['expiry_month'] = ['required', new CardExpirationMonth($this->get('expiry_month'))];
        $rules['expiry_year']  = ['required', new CardExpirationYear($this->get('expiry_year'))];

        if (App::environment(['production'])) {
            return $rules;
        }

        $rules['expiry_month'] = [
            'required',
            function ($attribute, $value, $fail) {
                if (!in_array($this->number, self::TEST_CARD_NUMBERS)) {
                    $fail($attribute . ' is invalid.');
                }

                return new CardExpirationMonth($this->get('expiry_month'));
            },
        ];

        $rules['expiry_year'] = [
            'required',
            function ($attribute, $value, $fail) {
                if (!in_array($this->number, self::TEST_CARD_NUMBERS)) {
                    $fail($attribute . ' is invalid.');
                }

                return new CardExpirationYear($this->get('expiry_year'));
            },
        ];

        return $rules;
    }

    /**
     * @return \Omnipay\Common\CreditCard
     */
    public function getCreditCard(): CreditCard
    {
        $camelCased = collect($this->validated())->keyBy(function ($value, $key) {
            return camel_case($key);
        })->toArray();

        return new CreditCard($camelCased);
    }
}
