<?php

namespace App\Components\Finance;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\VO\CreateInvoicePaymentsData;
use App\Components\Finance\Models\VO\CreatePaymentData;
use App\Components\Finance\Models\VO\GLAccountListItem;
use App\Components\Finance\Models\VO\PaymentInvoiceItem;
use App\Helpers\Decimal;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use App\Exceptions\Api\FailedDependencyException;

/**
 * Class CreditCardPaymentProcessor
 *
 * @package App\Components\Finance
 */
final class CreditCardPaymentProcessor
{
    /**
     * @var GatewayInterface
     */
    private $gateway;

    /**
     * @var CreditCard
     */
    private $creditCard;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * CreditCardPaymentProcessor constructor.
     *
     * @param \Omnipay\Common\GatewayInterface $gateway
     */
    public function __construct(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param \Omnipay\Common\CreditCard $creditCard
     *
     * @return self
     */
    public function setCreditCard(CreditCard $creditCard): self
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    /**
     * @return \App\Components\Finance\Models\Invoice
     */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    /**
     * @param \App\Components\Finance\Models\Invoice $invoice
     *
     * @return self
     */
    public function setInvoice(Invoice $invoice): self
    {
        if ($invoice->isDraft()) {
            throw new NotAllowedException('Invoice is not approved.');
        }

        if ($invoice->isPaidInFull()) {
            throw new NotAllowedException('Invoice has been paid in full already.');
        }

        if (Decimal::lt($invoice->getAmountDue(), 1)) {
            throw new NotAllowedException('Invoice payment amount must be at least 1 dollar.');
        }

        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return \Omnipay\Common\CreditCard
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @param int $userId User identifier who initiate payment.
     *
     * @return \App\Components\Finance\Models\VO\CreateInvoicePaymentsData
     *
     * @throws \JsonMapper_Exception
     */
    final public function createInvoicePaymentData(int $userId): CreateInvoicePaymentsData
    {
        if (null === $this->invoice || null === $this->creditCard) {
            throw new NotAllowedException('Invoice and credit card must be set.');
        }

        $invoice = $this->invoice;

        $invoicePaymentData = new CreateInvoicePaymentsData();

        $invoicePaymentData->invoices_list[] = new PaymentInvoiceItem([
            'invoice_id' => $invoice->id,
            'amount'     => $invoice->getAmountDue(),
        ]);

        $organizationId = $invoice->accounting_organization_id;

        $paymentData = (new CreatePaymentData())
            ->setAccountingOrganizationId($organizationId)
            ->setAmount($invoice->getAmountDue())
            ->setReference($invoice->reference)
            ->setUserId($userId);

        try {
            $clearingGLAccount = GLAccount::withCode(GLAccount::CLEARING_ACCOUNT_CODE, $organizationId)
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new NotAllowedException('There is no Clearing Account for the accounting organization.');
        }

        $clearingGLAccountListItem = new GLAccountListItem([
            'glAccount' => $clearingGLAccount,
            'amount'    => $paymentData->getAmount(),
        ]);

        /**
         * TODO refactor it after discussion. I need decrease balance for receivable account and that\'s why
         * TODO I add $clearingGLAccountListItem to payableGLAccountList and it seems strange.
         *
         * @see PaymentService::processPayment implementation
         */

        $paymentData->payableGLAccountList = [$clearingGLAccountListItem];

        try {
            $bankAccount = GLAccount::withBankAccount($organizationId)
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new NotAllowedException('There is no Trading Bank Account for the accounting organization.');
        }

        $bankGLAccountListItem = new GLAccountListItem([
            'glAccount' => $bankAccount,
            'amount'    => $paymentData->getAmount(),
        ]);

        $paymentData->receivableGLAccountList = [$bankGLAccountListItem];

        $invoicePaymentData->setPaymentData($paymentData);

        return $invoicePaymentData;
    }

    /**
     * Process payment with credit card
     *
     * @param \App\Components\Finance\Models\VO\CreatePaymentData $paymentData
     *
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    final public function process(CreatePaymentData $paymentData): ResponseInterface
    {
        $invoice                = $this->invoice;
        $accountingOrganization = AccountingOrganization::findOrFail($paymentData->getAccountingOrganizationId());
        $paymentAmount          = round($paymentData->getAmount(), 2);

        $additionalInfo = sprintf('Payment for invoice #%d', $invoice->id);

        $description = null === $paymentData->getReference()
            ? $additionalInfo
            : sprintf('%s. %s', $paymentData->getReference(), $additionalInfo);

        try {
            $transaction = $this->gateway->purchase([
                'amount'      => $paymentAmount,
                'description' => $description,
                'card'        => $this->getCreditCard(),
                'secretKey'   => $accountingOrganization->cc_payments_api_key,
                'capture'     => false,
                'metadata'    => ['invoice_id' => $invoice->id],
            ]);

            $response = $transaction->send();
        } catch (Exception $e) {
            Log::info(
                sprintf('Purchase operation for invoice [ID:%d] has been failed.', $invoice->id),
                [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'trace'   => $e->getTraceAsString(),
                ]
            );

            throw new FailedDependencyException($e->getMessage());
        }

        if (!$response->isSuccessful()) {
            $parameters = $transaction->getParameters();
            unset($parameters['secretKey']);
            $errorData = [
                'parameters'            => $parameters,
                'card'                  => $this->getCreditCard()->getNumberLastFour(),
                'transaction_reference' => $response->getTransactionReference(),
                'code'                  => $response->getCode(),
                'message'               => $response->getMessage(),
                'data'                  => $response->getData(),
                'request'               => $response->getRequest(),
            ];
            Log::info(
                sprintf(
                    'Response for purchase for invoice [ID:%d] is not successful.',
                    $invoice->id
                ),
                $errorData
            );
            throw new FailedDependencyException($response->getMessage());
        }

        return $response;
    }

    /**
     * Captures the payment created after purchase action.
     *
     * @param \App\Components\Finance\Models\VO\CreatePaymentData $paymentData
     * @param string                                              $token
     *
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    final public function capture(CreatePaymentData $paymentData, string $token): ResponseInterface
    {
        $accountingOrganization = AccountingOrganization::findOrFail($paymentData->getAccountingOrganizationId());
        $paymentAmount          = round($paymentData->getAmount(), 2);

        try {
            $transaction = $this->gateway->capture([
                'token'     => $token,
                'secretKey' => $accountingOrganization->cc_payments_api_key,
                'amount'    => $paymentAmount,
            ]);

            $response = $transaction->send();
        } catch (Exception $e) {
            Log::info(
                sprintf('Capture operation for invoice [ID:%d] has been failed.', $this->invoice->id),
                [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'trace'   => $e->getTraceAsString(),
                ]
            );

            throw new FailedDependencyException($e->getMessage());
        }

        if (!$response->isSuccessful()) {
            $parameters = $transaction->getParameters();
            unset($parameters['secretKey']);
            $errorData = [
                'parameters'            => $parameters,
                'card'                  => $this->getCreditCard()->getNumberLastFour(),
                'transaction_reference' => $response->getTransactionReference(),
                'code'                  => $response->getCode(),
                'message'               => $response->getMessage(),
                'data'                  => $response->getData(),
                'request'               => $response->getRequest(),
            ];
            Log::info(
                sprintf('Capture payment for invoice [ID:%d] via credit card has failed.', $this->invoice->id),
                $errorData
            );
            throw new FailedDependencyException($response->getMessage());
        }

        return $response;
    }
}
