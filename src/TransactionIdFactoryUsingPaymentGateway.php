<?php

namespace Clapp\OtpHu;

use Clapp\OtpHu\Contract\TransactionIdFactoryContract;
use Clapp\OtpHu\Request\GenerateTransactionIdRequest;

class TransactionIdFactoryUsingPaymentGateway extends Gateway implements TransactionIdFactoryContract
{
    public $lastResponse = null;

    public function generateTransactionId($parameters = [])
    {
        $response = $this->getGenerateTransactionIdRequest($parameters)->send();
        $this->lastResponse = $response;

        return $response->getTransactionId();
    }

    protected function getGenerateTransactionIdRequest($parameters)
    {
        $request = $this->createRequest('\\'.GenerateTransactionIdRequest::class, $parameters);

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint'
        );

        return $request;
    }
}
