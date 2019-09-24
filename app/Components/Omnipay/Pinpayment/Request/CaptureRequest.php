<?php

namespace App\Components\Omnipay\Pinpayment\Request;

use App\Components\Omnipay\Pinpayment\Response;

/**
 * Class CaptureRequest
 * The charges API allows you to capture charge created by purchase with capture = false value.
 *
 * Gateway Parameters
 * * amount    Amount to charge in the currency’s base unit
 *
 * <code>
 *
 * This card can be used for testing.
 *
 * @see     https://pin.net.au/docs/api/test-cards for a list of card
 *
 * Do a capture transaction on the gateway
 * $transaction = $gateway->capture(array(
 *     'amount'                   => '10.00',
 *     'token'                   => token from purchase response,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>.
 *
 * @see     \App\Components\Omnipay\Pinpayment\Gateway
 * @package App\Components\Omnipay\Pinpayment\Request
 * @link    https://pin.net.au/docs/api/charges
 */
class CaptureRequest extends AbstractRequest
{
    /**
     * Get the raw data array for this message.
     *
     * @return mixed
     *
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData(): array
    {
        $this->validate('token');
        $amount = $this->getAmountInteger();

        return $amount ? ['amount' => $amount] : [];
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return \App\Components\Omnipay\Pinpayment\Response
     */
    public function sendData($data): Response
    {
        $httpResponse = $this->sendRequest(
            sprintf('/charges/%s/capture', $this->getToken()),
            $data,
            'PUT'
        );

        $body = $httpResponse->getBody()->getContents();

        return $this->response = new Response($this, json_decode($body, true));
    }
}
