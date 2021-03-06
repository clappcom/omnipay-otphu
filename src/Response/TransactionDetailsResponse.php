<?php

namespace Clapp\OtpHu\Response;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Clapp\OtpHu\BadResponseException;
use Clapp\OtpHu\TransactionFailedException;
use Exception;
use Clapp\OtpHu\Transaction;

class TransactionDetailsResponse extends AbstractResponse
{
    protected $transaction;

    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->transaction = $this->parseTransaction($data);
    }

    /**
     * transaction adatainak kiszedése a raw response-ból.
     */
    protected function parseTransaction($data)
    {
        try {
            $transaction = Transaction::fromXml($data);
        } catch (Exception $e) {
            throw new BadResponseException($data);
        }
        if ($transaction->isCompleted()) {
            if (empty($transaction->getRawTransaction()['responsecode'])) {
                throw new BadResponseException($data);
            }
            /*$responseCode = $transaction['responsecode'];
            if (intval($responseCode) > 10){
                throw new TransactionFailedException("", $responseCode);
            }*/
        }

        return $transaction;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @override
     */
    public function getTransactionId()
    {
        return $this->transaction->getTransactionId();
    }

    /**
     * Is the response successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if (empty($this->transaction)) {
            throw new Exception('no transaction details found');
        }
        return $this->transaction->isSuccessful();
    }

    /**
     * Is the response rejected?
     */
    public function isRejected()
    {
        if (empty($this->transaction)) {
            throw new Exception('no transaction details found');
        }
        return $this->transaction->isRejected();
    }

    /**
     * Is the transaction cancelled by the user?
     *
     * @return bool
     */
    public function isCancelled()
    {
        if (empty($this->transaction)) {
            throw new Exception('no transaction details found');
        }
        return $this->transaction->isCancelled();
    }

    /**
     * Is the response successful?
     *
     * @return bool
     */
    public function isPending()
    {
        if (empty($this->transaction)) {
            throw new Exception('no transaction details found');
        }
        return $this->transaction->isPending();
    }
}
