<?php

namespace App\Components\Omnipay\Pinpayment;

use App\Components\Omnipay\Pinpayment\Request\CaptureRequest;
use App\Components\Omnipay\Pinpayment\Request\PurchaseRequest;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\RequestInterface;

/**
 * Class Gateway
 * This interface class defines the standard functions that any Omnipay gateway needs to define.
 *
 * @see     AbstractGateway
 * @package App\Components\Omnipay\Pinpayment
 *
 * @method RequestInterface authorize(array $options = [])
 * @method RequestInterface completeAuthorize(array $options = [])
 * @method RequestInterface completePurchase(array $options = [])
 * @method RequestInterface refund(array $options = [])
 * @method RequestInterface void(array $options = [])
 * @method RequestInterface createCard(array $options = [])
 * @method RequestInterface updateCard(array $options = [])
 * @method RequestInterface deleteCard(array $options = [])
 */
class Gateway extends AbstractGateway implements GatewayInterface
{
    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     *
     * @return string
     */
    public function getName()
    {
        return 'pinpayment';
    }

    /**
     * @return string
     */
    public static function getClassName()
    {
        return '\\' . static::class;
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return config('omnipay.gateways.pinpayment.options');
    }

    /**
     * Implements purchase (charge) feature.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    /**
     * Implements capture feature.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(CaptureRequest::class, $parameters);
    }
}
