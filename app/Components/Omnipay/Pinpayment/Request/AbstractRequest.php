<?php

namespace App\Components\Omnipay\Pinpayment\Request;

/**
 * Class AbstractRequest
 * This is the parent class for all Pinpayment requests.
 *
 * Test modes:
 *
 * The API has two endpoint host names:
 *
 * * api.pin.net.au (live)
 * * test-api.pin.net.au (test)
 *
 * The live host is for processing live transactions, whereas the test
 * host can be used for integration testing and development.
 *
 * Each endpoint requires a different set of API keys, which can be
 * found in your account settings.
 *
 * Currently this class makes the assumption that if the testMode
 * flag is set then the Test Endpoint is being used.
 *
 * @package   App\Components\Omnipay\Pinpayment\Request
 * @see       \App\Components\Omnipay\Pinpayment\Gateway
 * @link      https://pin.net.au/docs/api
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    const API_VERSION = '1';
    /**
     * Test Endpoint URL
     *
     * @var string URL
     */
    protected $testEndpoint = 'https://test-api.pinpayments.com/';
    /**
     * Live Endpoint URL
     *
     * @var string URL
     */
    protected $liveEndpoint = 'https://api.pinpayments.com/';

    /**
     * Get secret key
     *
     * Calls to the Pin Payments API must be authenticated using HTTP
     * basic authentication, with your API key as the username, and
     * a blank string as the password.
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    /**
     * Set secret key
     *
     * Calls to the Pin Payments API must be authenticated using HTTP
     * basic authentication, with your API key as the username, and
     * a blank string as the password.
     *
     * @param string $value
     *
     * @return AbstractRequest implements a fluent interface
     */
    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * Get the request email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getParameter('email');
    }

    /**
     * Sets the request email.
     *
     * @param string $value
     *
     * @return AbstractRequest Provides a fluent interface
     */
    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    /**
     * Get API endpoint URL
     *
     * @return string
     */
    protected function getEndpoint()
    {
        $base = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;

        return $base . self::API_VERSION;
    }

    /**
     * Send a request to the gateway.
     *
     * @param string $action
     * @param array  $data
     * @param string $method
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendRequest($action, $data = null, $method = 'POST')
    {
        return $this->httpClient->request(
            $method,
            $this->getEndpoint() . $action,
            [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->getSecretKey() . ':'),
            ],
            json_encode($data)
        );
    }
}
