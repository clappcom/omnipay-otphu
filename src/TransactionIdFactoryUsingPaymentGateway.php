<?php

namespace Clapp\OtpHu;

use Clapp\OtpHu\Contract\TransactionIdFactoryContract;
use Clapp\OtpHu\Request\GenerateTransactionIdRequest;

class TransactionIdFactoryUsingPaymentGateway extends Gateway implements TransactionIdFactoryContract{

    public function generateTransactionId($parameters = []){
        $request = $this->getGenerateTransactionIdRequest($parameters);
        return $request->send()->getTransactionId();
    }

    protected function getGenerateTransactionIdRequest($parameters){
        $request = $this->createRequest("\\".GenerateTransactionIdRequest::class, $parameters);

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint'
        );
        return $request;
    }
}
