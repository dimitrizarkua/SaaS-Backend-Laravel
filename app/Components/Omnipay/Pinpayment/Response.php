<?php

namespace App\Components\Omnipay\Pinpayment;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Class Response
 * Response class for all Pinpayment requests.
 *
 * @see     \App\Components\Omnipay\Pinpayment\Gateway
 * @package App\Components\Omnipay\Pinpayment
 */
class Response extends AbstractResponse
{
    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return !isset($this->data['error']);
    }

    /**
     * @return null|string A reference provided by the gateway to represent this transaction.
     */
    public function getTransactionReference()
    {
        if (isset($this->data['response']['token'])) {
            return $this->data['response']['token'];
        }

        return null;
    }

    /**
     * This is used after createCard to get the credit card token to be used in future transactions.
     *
     * @return null|string
     */
    public function getCardReference()
    {
        if (isset($this->data['response']['token'])) {
            return $this->data['response']['token'];
        }

        return null;
    }

    /**
     * @deprecated
     */
    public function getCardToken()
    {
        return $this->getCardReference();
    }

    /**
     * This is used after createCustomer to get the customer token to be used in future transactions.
     *
     * @return string
     */
    public function getCustomerReference()
    {
        if (isset($this->data['response']['token'])) {
            return $this->data['response']['token'];
        }

        return '';
    }

    /**
     * @return null|string A response message from the payment gateway.
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            if (isset($this->data['response']['status_message'])) {
                return $this->data['response']['status_message'];
            } else {
                return true;
            }
        } else {
            return $this->data['error_description'];
        }
    }

    /**
     * @return null|string A response code from the payment gateway.
     */
    public function getCode()
    {
        if (isset($this->data['error'])) {
            return $this->data['error'];
        }

        return null;
    }
}
